// Content script - runs on CRM pages
console.log('CRM Screenshot Monitor - Content script loaded');

// Get user ID from page
function getUserId() {
    // Try to get from meta tag
    const metaUser = document.querySelector('meta[name="user-id"]');
    if (metaUser) {
        return metaUser.content;
    }

    // Try to get from auth user object
    if (window.Laravel && window.Laravel.user) {
        return window.Laravel.user.id;
    }

    return null;
}

// Listen for attendance changes on the page
function detectAttendanceId() {
    let attendanceId = null;
    let userId = getUserId();

    // Try to get attendance ID from Livewire component
    if (window.Livewire) {
        try {
            const components = window.Livewire.all();
            for (let component of components) {
                if (component.currentAttendanceId) {
                    attendanceId = component.currentAttendanceId;
                    break;
                }
            }
        } catch (e) {
            console.log('Could not access Livewire components');
        }
    }

    // If we have both user ID and attendance ID, notify background
    if (userId && attendanceId) {
        console.log('✓ User ID:', userId, 'Attendance ID:', attendanceId);

        // Update background script
        chrome.runtime.sendMessage({
            action: 'updateUserId',
            userId: parseInt(userId)
        });

        chrome.runtime.sendMessage({
            action: 'updateAttendance',
            attendanceId: parseInt(attendanceId)
        });

        // Trigger auto-start
        chrome.runtime.sendMessage({
            action: 'crmPageOpened',
            userId: parseInt(userId),
            attendanceId: parseInt(attendanceId)
        });
    } else if (userId) {
        // Only have user ID
        console.log('✓ User ID:', userId, '(Waiting for attendance ID...)');
        chrome.runtime.sendMessage({
            action: 'updateUserId',
            userId: parseInt(userId)
        });
    }
}

// Check for user/attendance when page loads
window.addEventListener('load', () => {
    console.log('CRM page loaded, detecting user and attendance...');
    setTimeout(detectAttendanceId, 2000);
});

// Also check when Livewire is ready
document.addEventListener('livewire:load', () => {
    console.log('Livewire loaded, detecting attendance...');
    setTimeout(detectAttendanceId, 1000);
});

// Check periodically for attendance changes
setInterval(detectAttendanceId, 30000); // Every 30 seconds
