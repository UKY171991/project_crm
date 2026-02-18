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
const screenshotCountEl = document.getElementById('screenshotCount');
const lastCaptureEl = document.getElementById('lastCapture');
const captureStatusEl = document.getElementById('captureStatus');

const CRM_BASE_URL = 'https://crm.devloper.space';

let localWorkSeconds = 0;
let workTimerInterval = null;

// Initialize
chrome.storage.local.get(['userId', 'attendanceId', 'isCapturing', 'userName', 'screenshotCount', 'lastCapture'], (result) => {
    if (result.userId) {
        userIdInput.value = result.userId;
        userIdDisplayEl.textContent = result.userId;
        fetchWorkStats(result.userId);
    }
    if (result.attendanceId) {
        attendanceIdInput.value = result.attendanceId;
        attendanceIdDisplayEl.textContent = result.attendanceId;
    }
    if (result.userName) userNameEl.textContent = result.userName;
    if (result.screenshotCount !== undefined) screenshotCountEl.textContent = result.screenshotCount;
    if (result.lastCapture) lastCaptureEl.textContent = result.lastCapture;

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

            // SCREENSHOT DISABLED: Skip chooseDesktopMedia
            chrome.runtime.sendMessage({
                action: 'startCapture',
                userId: parseInt(uId),
                attendanceId: parseInt(aId),
                streamId: null // No stream needed for time-only tracking
            }, (response) => {
                if (response && response.success) {
                    updateUI(true, uId, aId);
                    startLocalTicker();
                }
                startBtn.disabled = false;
                startBtn.textContent = '▶️ Start Time Recording';
            });
        } else {
            alert('Server failed to start session.');
            startBtn.disabled = false;
            startBtn.textContent = '▶️ Start Time Recording';
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
    chrome.runtime.sendMessage({ action: 'stopCapture' }, () => {
        clearInterval(workTimerInterval);
        updateUI(false, uId, attendanceIdInput.value);
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
            chrome.storage.local.set({ userId: data.id, userName: data.name });
            userIdInput.value = data.id;
            userNameEl.textContent = data.name;
            userIdDisplayEl.textContent = data.id;
            await fetchWorkStats(data.id);
            updateUI(false, data.id, null);
        } else showError('Invalid login.');
    } catch (e) { showError('Network error'); }
    extLoginBtn.disabled = false;
};

logoutBtn.onclick = () => {
    if (!confirm('Logout?')) return;
    chrome.runtime.sendMessage({ action: 'stopCapture' });
    chrome.storage.local.clear(() => location.reload());
};

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
        statsSection.style.display = 'block';
        liveTimeCard.style.display = 'block';

        if (isCapturing) {
            statusDiv.className = 'status active';
            statusDiv.innerHTML = '<span class="status-icon">✅</span><span>Recording Time</span>';
            stopBtn.style.display = 'block';
            captureStatusEl.textContent = 'Active';
        } else {
            statusDiv.className = 'status inactive';
            statusDiv.innerHTML = '<span class="status-icon">⏸️</span><span>Idle</span>';
            stopBtn.style.display = 'none';
            captureStatusEl.textContent = 'Idle';
            startBtn.textContent = '▶️ Start Time Recording';
        }
    }
}

function showError(msg) {
    loginErrorEl.textContent = msg;
    loginErrorEl.style.display = 'block';
}

setInterval(() => {
    chrome.storage.local.get(['screenshotCount', 'lastCapture', 'isCapturing', 'userId'], (res) => {
        if (res.userId) {
            if (res.screenshotCount !== undefined) screenshotCountEl.textContent = res.screenshotCount;
            if (res.lastCapture) lastCaptureEl.textContent = res.lastCapture;
        }
    });
}, 3000);
