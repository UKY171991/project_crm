// Popup script
const openCrmBtn = document.getElementById('openCrmBtn');
const startBtn = document.getElementById('startBtn');
const stopBtn = document.getElementById('stopBtn');
const userIdInput = document.getElementById('userId');
const attendanceIdInput = document.getElementById('attendanceId');
const statusDiv = document.getElementById('status');
const userInfoDiv = document.getElementById('userInfo');
const inputFormDiv = document.getElementById('inputForm');
const statsSection = document.getElementById('statsSection');
const extLoginForm = document.getElementById('extLoginForm');
const logoutBtn = document.getElementById('logoutBtn');

// Time Display elements
const liveTimeCard = document.getElementById('liveTimeCard');
const extCurrentTime = document.getElementById('extCurrentTime');
const extWorkTime = document.getElementById('extWorkTime');

// Login form elements
const extEmailInput = document.getElementById('extEmail');
const extPasswordInput = document.getElementById('extPassword');
const extLoginBtn = document.getElementById('extLoginBtn');
const loginErrorEl = document.getElementById('loginError');

// Display elements
const userNameEl = document.getElementById('userName');
const userIdDisplayEl = document.getElementById('userIdDisplay');
const attendanceIdDisplayEl = document.getElementById('attendanceIdDisplay');
const activityCountEl = document.getElementById('activityCount');
const lastActivityEl = document.getElementById('lastActivity');
const captureStatusEl = document.getElementById('captureStatus');

// Projects and Payments elements
const projectsSection = document.getElementById('projectsSection');
const projectsList = document.getElementById('projectsList');
const paymentsSection = document.getElementById('paymentsSection');
const paymentsList = document.getElementById('paymentsList');

const CRM_BASE_URL = 'https://crm.devloper.space';

let localWorkSeconds = 0;
let workTimerInterval = null;

// Initialize
chrome.storage.local.get(['userId', 'attendanceId', 'isCapturing', 'userName', 'activityCount', 'lastActivity', 'userRole'], (result) => {
    if (result.userId) {
        userIdInput.value = result.userId;
        userIdDisplayEl.textContent = result.userId;
        fetchWorkStats(result.userId);
        
        // Fetch projects and payments
        fetchProjects(result.userId, result.userRole);
        if (result.userRole === 'master' || result.userRole === 'admin') {
            fetchPendingPayments();
        }
        
        // Auto-start tracking if user is logged in and not already tracking
        if (!result.isCapturing) {
            autoStartCapture(result.userId);
        }
    }
    if (result.attendanceId) {
        attendanceIdInput.value = result.attendanceId;
        attendanceIdDisplayEl.textContent = result.attendanceId;
    }
    if (result.userName) userNameEl.textContent = result.userName;
    if (result.activityCount !== undefined) activityCountEl.textContent = result.activityCount;
    if (result.lastActivity) lastActivityEl.textContent = result.lastActivity;

    updateUI(result.isCapturing || false, result.userId, result.attendanceId);
});

// Clock
setInterval(() => {
    extCurrentTime.textContent = new Date().toLocaleTimeString();
}, 1000);

function startLocalTicker() {
    if (workTimerInterval) clearInterval(workTimerInterval);
    workTimerInterval = setInterval(() => {
        localWorkSeconds++;
        updateWorkDisplay(localWorkSeconds);
    }, 1000);
}

function updateWorkDisplay(totalSecs) {
    const total = Math.floor(totalSecs);
    const h = Math.floor(total / 3600);
    const m = Math.floor((total % 3600) / 60);
    const s = total % 60;
    extWorkTime.textContent = `${h}h ${m}m ${s}s`;
}

async function fetchWorkStats(uId) {
    if (!uId) return;
    try {
        const response = await fetch(`${CRM_BASE_URL}/api/get-work-stats`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: uId })
        });
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                localWorkSeconds = Math.floor(data.net_seconds || 0);
                updateWorkDisplay(localWorkSeconds);
                chrome.storage.local.get(['isCapturing'], (res) => {
                    if (res.isCapturing) startLocalTicker();
                });
            }
        }
    } catch (e) { }
}

async function autoStartCapture(uId) {
    if (!uId) return;

    try {
        const res = await fetch(`${CRM_BASE_URL}/api/get-active-attendance`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: uId, auto_start: true })
        });
        const data = await res.json();

        if (data.success && data.attendance_id) {
            const aId = data.attendance_id;
            attendanceIdInput.value = aId;
            attendanceIdDisplayEl.textContent = aId;
            chrome.storage.local.set({ attendanceId: aId });

            // Auto-start tracking (no permission needed)
            chrome.runtime.sendMessage({
                action: 'startCapture',
                userId: parseInt(uId),
                attendanceId: parseInt(aId)
            }, (response) => {
                if (response && response.success) {
                    updateUI(true, uId, aId);
                    startLocalTicker();
                }
            });
        }
    } catch (e) {
        console.log('Auto-start status:', e.message);
    }
}

startBtn.onclick = async () => {
    const uId = userIdInput.value;
    if (!uId) return alert('Please Login');

    startBtn.disabled = true;
    startBtn.textContent = '⏱️ Starting...';

    try {
        const res = await fetch(`${CRM_BASE_URL}/api/get-active-attendance`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: uId, auto_start: true })
        });
        const data = await res.json();

        if (data.success && data.attendance_id) {
            const aId = data.attendance_id;
            attendanceIdInput.value = aId;
            attendanceIdDisplayEl.textContent = aId;
            chrome.storage.local.set({ attendanceId: aId });

            // Request Screen Selection for "Whole Screen" capture - ENTIRE SCREEN
            chrome.desktopCapture.chooseDesktopMedia(['screen'], (streamId) => {
                if (!streamId) {
                    alert('Screen capture selection is required for monitoring.');
                    startBtn.disabled = false;
                    startBtn.textContent = '▶️ Start Monitoring';
                    return;
                }

                // Mark for auto-restart
                chrome.storage.local.set({ wasCapturingBeforeClose: true });

                chrome.runtime.sendMessage({
                    action: 'startCapture',
                    userId: parseInt(uId),
                    attendanceId: parseInt(aId),
                    streamId: streamId
                }, (response) => {
                    if (response && response.success) {
                        updateUI(true, uId, aId);
                        startLocalTicker();
                    }
                    startBtn.disabled = false;
                    startBtn.textContent = '▶️ Start Monitoring';
                });
            });
        } else {
            alert('Server failed to start session.');
            startBtn.disabled = false;
            startBtn.textContent = '▶️ Start Monitoring';
        }
    } catch (e) {
        alert('Server connection error.');
        startBtn.disabled = false;
        startBtn.textContent = '▶️ Start Time Recording';
    }
};

openCrmBtn.onclick = () => {
    chrome.tabs.create({ url: CRM_BASE_URL });
    window.close(); // Close popup
};

stopBtn.onclick = () => {
    const uId = userIdInput.value;
    // Manual stop - clock out and clear auto-restart
    chrome.runtime.sendMessage({ action: 'stopCapture' }, () => {
        clearInterval(workTimerInterval);
        chrome.storage.local.set({ wasCapturingBeforeClose: false });
        updateUI(false, uId, attendanceIdInput.value);
    });
};

manualScreenshotBtn.onclick = () => {
    const uId = userIdInput.value;
    const aId = attendanceIdInput.value;
    
    if (!uId || !aId) {
        alert('Please start monitoring first');
        return;
    }
    
    manualScreenshotBtn.disabled = true;
    manualScreenshotBtn.textContent = '📸 Capturing...';
    
    // Request screen capture for manual screenshot
    chrome.desktopCapture.chooseDesktopMedia(['screen'], (streamId) => {
        if (streamId) {
            // Send message to background to capture screenshot
            chrome.runtime.sendMessage({
                action: 'manualScreenshot',
                streamId: streamId,
                userId: parseInt(uId),
                attendanceId: parseInt(aId)
            }, (response) => {
                if (response && response.success) {
                    manualScreenshotBtn.textContent = '✅ Screenshot Taken!';
                    setTimeout(() => {
                        manualScreenshotBtn.textContent = '📸 Take Screenshot Now';
                        manualScreenshotBtn.disabled = false;
                    }, 2000);
                } else {
                    manualScreenshotBtn.textContent = '❌ Failed';
                    setTimeout(() => {
                        manualScreenshotBtn.textContent = '📸 Take Screenshot Now';
                        manualScreenshotBtn.disabled = false;
                    }, 2000);
                }
            });
        } else {
            manualScreenshotBtn.textContent = '📸 Take Screenshot Now';
            manualScreenshotBtn.disabled = false;
        }
    });
};

extLoginBtn.onclick = async () => {
    const email = extEmailInput.value;
    const password = extPasswordInput.value;
    if (!email || !password) return showError('Enter credentials');
    extLoginBtn.disabled = true;
    try {
        const res = await fetch(`${CRM_BASE_URL}/api/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });
        const data = await res.json();
        if (data.success) {
            chrome.storage.local.set({ 
                userId: data.id, 
                userName: data.name,
                userRole: data.role || 'user'
            });
            userIdInput.value = data.id;
            userNameEl.textContent = data.name;
            userIdDisplayEl.textContent = data.id;
            await fetchWorkStats(data.id);
            updateUI(false, data.id, null);
            
            // Fetch projects and payments
            fetchProjects(data.id, data.role);
            if (data.role === 'master' || data.role === 'admin') {
                fetchPendingPayments();
            }
            
            // Auto-start capture after login
            setTimeout(() => autoStartCapture(data.id), 500);
        } else showError('Invalid login.');
    } catch (e) { showError('Network error'); }
    extLoginBtn.disabled = false;
};

async function fetchProjects(userId, userRole) {
    if (!userId) return;
    
    projectsSection.style.display = 'block';
    projectsList.innerHTML = '<div class="loading">Loading projects...</div>';
    
    console.log('Fetching projects for user:', userId, 'role:', userRole);
    
    try {
        const response = await fetch(`${CRM_BASE_URL}/api/get-projects`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                user_id: userId,
                role: userRole || 'user'
            })
        });
        
        console.log('Projects response status:', response.status);
        
        if (response.ok) {
            const data = await response.json();
            console.log('Projects data:', data);
            
            if (data.success && data.projects && data.projects.length > 0) {
                displayProjects(data.projects);
            } else {
                projectsList.innerHTML = '<div class="empty-state">No active projects</div>';
            }
        } else {
            const errorText = await response.text();
            console.error('Projects fetch failed:', errorText);
            projectsList.innerHTML = '<div class="empty-state">Failed to load projects</div>';
        }
    } catch (e) {
        console.error('Failed to fetch projects:', e);
        projectsList.innerHTML = '<div class="empty-state">Error loading projects</div>';
    }
}

function displayProjects(projects) {
    projectsList.innerHTML = '';
    projects.forEach(project => {
        const projectDiv = document.createElement('div');
        projectDiv.className = `project-item ${project.status.toLowerCase()}`;
        
        const statusClass = project.status.toLowerCase() === 'running' ? 'running' : 'pending';
        
        projectDiv.innerHTML = `
            <div class="project-name">${project.name}</div>
            <span class="project-status ${statusClass}">${project.status}</span>
            <div class="project-client">Client: ${project.client_name || 'N/A'}</div>
        `;
        
        projectsList.appendChild(projectDiv);
    });
}

async function fetchPendingPayments() {
    paymentsSection.style.display = 'block';
    paymentsList.innerHTML = '<div class="loading">Loading payments...</div>';
    
    try {
        const response = await fetch(`${CRM_BASE_URL}/api/get-pending-payments`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.success && data.payments && data.payments.length > 0) {
                displayPayments(data.payments);
            } else {
                paymentsList.innerHTML = '<div class="empty-state">No pending payments</div>';
            }
        } else {
            paymentsList.innerHTML = '<div class="empty-state">Failed to load payments</div>';
        }
    } catch (e) {
        console.error('Failed to fetch payments:', e);
        paymentsList.innerHTML = '<div class="empty-state">Error loading payments</div>';
    }
}

function displayPayments(payments) {
    paymentsList.innerHTML = '';
    payments.forEach(payment => {
        const paymentDiv = document.createElement('div');
        paymentDiv.className = 'payment-item';
        
        paymentDiv.innerHTML = `
            <div class="payment-amount">${payment.currency || '$'} ${payment.amount}</div>
            <div class="payment-project">Project: ${payment.project_name}</div>
            <div class="payment-project">Client: ${payment.client_name || 'N/A'}</div>
        `;
        
        paymentsList.appendChild(paymentDiv);
    });
}

logoutBtn.onclick = () => {
    if (!confirm('Logout?')) return;
    chrome.runtime.sendMessage({ action: 'stopCapture' });
    // Clear all data including auto-restart flag
    chrome.storage.local.clear(() => location.reload());
};

function updateUI(isCapturing, userId, attendanceId) {
    if (!userId) {
        extLoginForm.style.display = 'block';
        inputFormDiv.style.display = 'none';
        userInfoDiv.style.display = 'none';
        statsSection.style.display = 'none';
        projectsSection.style.display = 'none';
        paymentsSection.style.display = 'none';
        statusDiv.style.display = 'none';
        liveTimeCard.style.display = 'none';
    } else {
        extLoginForm.style.display = 'none';
        inputFormDiv.style.display = isCapturing ? 'none' : 'block';
        userInfoDiv.style.display = 'block';
        statusDiv.style.display = 'block';
        statsSection.style.display = 'block';
        liveTimeCard.style.display = 'block';

        if (isCapturing) {
            statusDiv.className = 'status active';
            statusDiv.innerHTML = '<span class="status-icon">✅</span><span>Tracking Active</span>';
            stopBtn.style.display = 'block';
            captureStatusEl.textContent = 'Active';
        } else {
            statusDiv.className = 'status inactive';
            statusDiv.innerHTML = '<span class="status-icon">⏸️</span><span>Idle</span>';
            stopBtn.style.display = 'none';
            captureStatusEl.textContent = 'Idle';
            startBtn.textContent = '▶️ Start Tracking';
        }
    }
}

setInterval(() => {
    chrome.storage.local.get(['activityCount', 'lastActivity', 'isCapturing', 'userId'], (res) => {
        if (res.userId) {
            if (res.activityCount !== undefined) activityCountEl.textContent = res.activityCount;
            if (res.lastActivity) lastActivityEl.textContent = res.lastActivity;
        }
    });
}, 3000);
