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

// Extension Login Handle
extLoginBtn.addEventListener('click', async () => {
    const email = extEmailInput.value;
    const password = extPasswordInput.value;

    if (!email || !password) {
        showError('Please enter both email and password');
        return;
    }

    extLoginBtn.disabled = true;
    extLoginBtn.textContent = 'Logging in...';
    loginErrorEl.style.display = 'none';

    try {
        const response = await fetch(`${CRM_BASE_URL}/api/login`, {
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
                // Save and display user info
                chrome.storage.local.set({
                    userId: data.id,
                    userName: data.name
                });

                userIdInput.value = data.id;
                userNameEl.textContent = data.name;
                userIdDisplayEl.textContent = data.id;

                // Try to get attendance ID automatically
                await fetchActiveAttendance(data.id);

                updateUI(false, data.id, attendanceIdInput.value);
            } else {
                showError(data.message || 'Login failed.');
            }
        } else {
            // Check for specific error status
            if (response.status === 404) {
                showError('Error: API not found. Please upload latest code to server.');
            } else if (response.status === 401) {
                showError('Invalid email or password.');
            } else {
                showError(`Server error: ${response.status}`);
            }
        }
    } catch (e) {
        showError('Network Error: Check internet or SSL.');
        console.error('Fetch error:', e);
    } finally {
        extLoginBtn.disabled = false;
        extLoginBtn.textContent = 'Login';
    }
});

// Logout Handle
logoutBtn.addEventListener('click', () => {
    if (confirm('Are you sure you want to logout? This will stop any active capturing.')) {
        chrome.runtime.sendMessage({ action: 'stopCapture' });
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
            body: JSON.stringify({ user_id: userId })
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
    chrome.runtime.sendMessage({ action: 'stopCapture' }, (response) => {
        if (response && response.success) {
            const userId = userIdInput.value;
            const attendanceId = attendanceIdInput.value;
            updateUI(false, userId, attendanceId);
        }
    });
});

// Update UI based on capture status
function updateUI(isCapturing, userId, attendanceId) {
    // Determine which main view to show
    if (!userId) {
        extLoginForm.style.display = 'block';
        inputFormDiv.style.display = 'none';
        userInfoDiv.style.display = 'none';
        statsSection.style.display = 'none';
        statusDiv.style.display = 'none';
    } else {
        extLoginForm.style.display = 'none';
        inputFormDiv.style.display = isCapturing ? 'none' : 'block';
        userInfoDiv.style.display = 'block';
        statusDiv.style.display = 'block';

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

// Update stats periodically when popup is open
setInterval(() => {
    chrome.storage.local.get(['screenshotCount', 'lastCapture', 'isCapturing'], (result) => {
        if (result.screenshotCount) {
            screenshotCountEl.textContent = result.screenshotCount;
        }
        if (result.lastCapture) {
            lastCaptureEl.textContent = result.lastCapture;
        }
        if (result.isCapturing) {
            captureStatusEl.textContent = 'Active';
        }
    });
}, 2000); // Update every 2 seconds
