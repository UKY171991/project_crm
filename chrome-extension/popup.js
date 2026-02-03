// Popup script
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

// New Time Display elements
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
const screenshotCountEl = document.getElementById('screenshotCount');
const lastCaptureEl = document.getElementById('lastCapture');
const captureStatusEl = document.getElementById('captureStatus');

const CRM_BASE_URL = 'https://crm.devloper.space';

// Load saved values and status
chrome.storage.local.get(['userId', 'attendanceId', 'isCapturing', 'userName', 'screenshotCount', 'lastCapture'], (result) => {
    if (result.userId) {
        userIdInput.value = result.userId;
        userIdDisplayEl.textContent = result.userId;
    }
    if (result.attendanceId) {
        attendanceIdInput.value = result.attendanceId;
        attendanceIdDisplayEl.textContent = result.attendanceId;
    }
    if (result.userName) {
        userNameEl.textContent = result.userName;
    }
    if (result.screenshotCount) {
        screenshotCountEl.textContent = result.screenshotCount;
    }
    if (result.lastCapture) {
        lastCaptureEl.textContent = result.lastCapture;
    }

    updateUI(result.isCapturing || false, result.userId, result.attendanceId);
});

// Update live clock
function updateClock() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    extCurrentTime.textContent = `${hours}:${minutes}:${seconds}`;
}
setInterval(updateClock, 1000);
updateClock();

// Fetch work stats from server
async function fetchWorkStats(userId) {
    if (!userId) return;
    try {
        const response = await fetch(`${CRM_BASE_URL}/api/get-work-stats`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        });
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                extWorkTime.textContent = data.work_display;
            }
        }
    } catch (e) {
        console.log('Work stats fetch failed:', e);
    }
}

// Extension Login Handle
extLoginBtn.addEventListener('click', async () => {
    const email = extEmailInput.value;
    const password = extPasswordInput.value;

    if (!email || !password) {
        showError('Please enter both email and password');
        return;
    }

    // Try multiple URL patterns in case of server routing issues
    const urls = [`${CRM_BASE_URL}/api/login`, `${CRM_BASE_URL}/login`];
    let found = false;

    for (const url of urls) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    chrome.storage.local.set({ userId: data.id, userName: data.name });
                    userIdInput.value = data.id;
                    userNameEl.textContent = data.name;
                    userIdDisplayEl.textContent = data.id;
                    await fetchActiveAttendance(data.id);
                    await fetchWorkStats(data.id);
                    updateUI(false, data.id, attendanceIdInput.value);
                    found = true;
                    break;
                }
            } else if (response.status === 401) {
                showError('Invalid email or password.');
                found = true;
                break;
            }
        } catch (e) {
            console.error(`Failed to connect to ${url}:`, e);
        }
    }

    if (!found) {
        showError('Could not find Login API. Please ensure files are uploaded to public_html/api');
    }

    extLoginBtn.disabled = false;
    extLoginBtn.textContent = 'Login';
});

// Logout Handle
logoutBtn.addEventListener('click', async () => {
    if (confirm('Are you sure you want to logout? This will stop any active capturing.')) {
        const userId = userIdInput.value;
        chrome.runtime.sendMessage({ action: 'stopCapture' });

        // Clock out on server
        if (userId) {
            try {
                await fetch(`${CRM_BASE_URL}/api/clock-out`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId })
                });
            } catch (e) { console.log('Clock-out on logout failed:', e); }
        }

        chrome.storage.local.clear(() => {
            // Restore defaults
            chrome.storage.local.set({ autoStart: true });
            location.reload();
        });
    }
});

function showError(msg) {
    loginErrorEl.textContent = msg;
    loginErrorEl.style.display = 'block';
}

async function fetchActiveAttendance(userId) {
    try {
        const response = await fetch(`${CRM_BASE_URL}/api/get-active-attendance`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_id: userId,
                auto_start: true // Extension will now start the clock!
            })
        });

        if (response.ok) {
            const data = await response.json();
            if (data.clocked_in) {
                attendanceIdInput.value = data.attendance_id;
                attendanceIdDisplayEl.textContent = data.attendance_id;
                chrome.storage.local.set({ attendanceId: data.attendance_id });
            }
        }
    } catch (e) {
        console.log('Could not fetch attendance:', e);
    }
}

// Start button click
startBtn.addEventListener('click', () => {
    const userId = userIdInput.value;
    const attendanceId = attendanceIdInput.value;

    if (!userId) {
        alert('Missing User ID. Please login again.');
        return;
    }

    if (!attendanceId) {
        alert('Please enter Attendance ID or clock in on the website.');
        return;
    }

    // Save values
    chrome.storage.local.set({ userId, attendanceId });

    // Send message to background script
    chrome.runtime.sendMessage({
        action: 'startCapture',
        userId: parseInt(userId),
        attendanceId: parseInt(attendanceId)
    }, (response) => {
        if (response && response.success) {
            updateUI(true, userId, attendanceId);
        }
    });
});

// Stop button click
stopBtn.addEventListener('click', () => {
    chrome.runtime.sendMessage({ action: 'stopCapture' }, async (response) => {
        if (response && response.success) {
            const userId = userIdInput.value;
            const attendanceId = attendanceIdInput.value;

            // Also call clock-out API
            try {
                await fetch(`${CRM_BASE_URL}/api/clock-out`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId })
                });
            } catch (e) { console.log('Clock-out failed:', e); }

            updateUI(false, userId, attendanceId);
        }
    });
});

// Update UI based on capture status
function updateUI(isCapturing, userId, attendanceId) {
    if (!userId) {
        extLoginForm.style.display = 'block';
        inputFormDiv.style.display = 'none';
        userInfoDiv.style.display = 'none';
        statsSection.style.display = 'none';
        statusDiv.style.display = 'none';
        liveTimeCard.style.display = 'none';
    } else {
        extLoginForm.style.display = 'none';
        inputFormDiv.style.display = isCapturing ? 'none' : 'block';
        userInfoDiv.style.display = 'block';
        statusDiv.style.display = 'block';
        liveTimeCard.style.display = 'block';

        fetchWorkStats(userId); // Initial fetch

        if (isCapturing) {
            statusDiv.className = 'status active';
            statusDiv.innerHTML = '<span class="status-icon">✅</span><span>Capturing Active</span>';
            stopBtn.style.display = 'block';
            statsSection.style.display = 'block';
            captureStatusEl.textContent = 'Active';
        } else {
            statusDiv.className = 'status inactive';
            statusDiv.innerHTML = '<span class="status-icon">⏸️</span><span>Not Capturing</span>';
            stopBtn.style.display = 'none';
            statsSection.style.display = 'none';
            captureStatusEl.textContent = 'Idle';
        }

        if (userId) userIdDisplayEl.textContent = userId;
        if (attendanceId) attendanceIdDisplayEl.textContent = attendanceId;
    }
}

// Update stats and work time periodically when popup is open
setInterval(() => {
    chrome.storage.local.get(['screenshotCount', 'lastCapture', 'isCapturing', 'userId'], (result) => {
        if (result.screenshotCount) {
            screenshotCountEl.textContent = result.screenshotCount;
        }
        if (result.lastCapture) {
            lastCaptureEl.textContent = result.lastCapture;
        }
        if (result.isCapturing) {
            captureStatusEl.textContent = 'Active';
        }
        if (result.userId) {
            fetchWorkStats(result.userId);
        }
    });
}, 5000); // Update every 5 seconds
