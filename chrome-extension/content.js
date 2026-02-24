// Helper to safely call extension APIs
function isContextValid() {
    try {
        return !!(chrome.runtime && chrome.runtime.id);
    } catch (e) {
        return false;
    }
}

// If we have both user ID and attendance ID, notify background
function notifyBackground(userId, attendanceId) {
    if (!isContextValid()) return;

    try {
        console.log('✓ User ID:', userId, 'Attendance ID:', attendanceId);

        chrome.runtime.sendMessage({
            action: 'updateUserId',
            userId: parseInt(userId)
        });

        chrome.runtime.sendMessage({
            action: 'updateAttendance',
            attendanceId: parseInt(attendanceId)
        });

        chrome.runtime.sendMessage({
            action: 'crmPageOpened',
            userId: parseInt(userId),
            attendanceId: parseInt(attendanceId)
        });
    } catch (e) { }
}

// Get user ID from page
function getUserId() {
    try {
        const metaUser = document.querySelector('meta[name="user-id"]');
        if (metaUser) return metaUser.content;
        if (window.Laravel && window.Laravel.user) return window.Laravel.user.id;
    } catch (e) { }
    return null;
}

// Listen for attendance changes on the page
function detectAttendanceId() {
    if (!isContextValid()) return;

    let attendanceId = null;
    let userId = getUserId();

    if (window.Livewire) {
        try {
            const components = window.Livewire.all();
            for (let component of components) {
                if (component.currentAttendanceId) {
                    attendanceId = component.currentAttendanceId;
                    break;
                }
            }
        } catch (e) { }
    }

    if (userId && attendanceId) {
        notifyBackground(userId, attendanceId);
    } else if (userId) {
        try {
            chrome.runtime.sendMessage({
                action: 'crmPageOpened',
                userId: parseInt(userId)
            });
        } catch (e) { }
    }
}

// Check for user/attendance when page loads
window.addEventListener('load', () => {
    if (!isContextValid()) return;
    setTimeout(() => {
        if (isContextValid()) detectAttendanceId();
    }, 2000);
});

// Also check when Livewire is ready
document.addEventListener('livewire:load', () => {
    if (!isContextValid()) return;
    setTimeout(() => {
        if (isContextValid()) detectAttendanceId();
    }, 1000);
});

// Check periodically for attendance changes
const attendanceInterval = setInterval(() => {
    if (!isContextValid()) {
        clearInterval(attendanceInterval);
        return;
    }
    detectAttendanceId();
}, 30000);
