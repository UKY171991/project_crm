// Background service worker - URL/Activity Tracking Only
let isCapturing = false;
let userId = null;
let attendanceId = null;
let heartbeatInterval = null;
let activityTrackingInterval = null;
let lastTrackedUrl = null;

const API_BASE = 'https://crm.devloper.space/api';

// Initial state sync - Auto-start on browser open
chrome.storage.local.get(['isCapturing', 'userId', 'attendanceId', 'wasCapturingBeforeClose'], (result) => {
  isCapturing = result.isCapturing || false;
  userId = result.userId || null;
  attendanceId = result.attendanceId || null;

  // Always show badge based on current state
  if (isCapturing) {
    chrome.action.setBadgeText({ text: 'ON' });
    chrome.action.setBadgeBackgroundColor({ color: '#4CAF50' });
  } else {
    chrome.action.setBadgeText({ text: 'OFF' });
    chrome.action.setBadgeBackgroundColor({ color: '#FF0000' });
  }

  // If user was capturing before browser closed, auto-restart
  if (result.wasCapturingBeforeClose && userId) {
    console.log('🔄 Browser reopened - Auto-restarting tracking');
    
    chrome.action.setBadgeText({ text: 'WAIT' });
    chrome.action.setBadgeBackgroundColor({ color: '#FFA500' });
    
    // Get or create attendance session
    fetch(`${API_BASE}/get-active-attendance?user_id=${userId}&auto_start=1`)
      .then(res => res.json())
      .then(data => {
        if (data.success && data.attendance_id) {
          attendanceId = data.attendance_id;
          isCapturing = true;
          chrome.storage.local.set({ 
            isCapturing: true,
            userId, 
            attendanceId,
            wasCapturingBeforeClose: true 
          });
          
          chrome.action.setBadgeText({ text: 'ON' });
          chrome.action.setBadgeBackgroundColor({ color: '#4CAF50' });
          
          startHeartbeat();
          startActivityTracking();
        }
      }).catch(err => {
        console.log('Auto-restart status:', err.message);
        chrome.action.setBadgeText({ text: 'ERR' });
        chrome.action.setBadgeBackgroundColor({ color: '#FF0000' });
      });
  } else if (isCapturing) {
    chrome.action.setBadgeText({ text: 'ON' });
    chrome.action.setBadgeBackgroundColor({ color: '#4CAF50' });
    startHeartbeat();
    startActivityTracking();
  } else {
    chrome.action.setBadgeText({ text: 'OFF' });
    chrome.action.setBadgeBackgroundColor({ color: '#FF0000' });
  }
});

// Keep state in sync
chrome.storage.onChanged.addListener((changes) => {
  if (changes.isCapturing) {
    isCapturing = changes.isCapturing.newValue;
  }
  if (changes.userId) {
    userId = changes.userId.newValue;
  }
  if (changes.attendanceId) {
    attendanceId = changes.attendanceId.newValue;
  }
});

chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
  if (message.action === 'startCapture' || message.action === 'crmPageOpened') {
    if (message.userId) userId = message.userId;
    if (message.attendanceId) attendanceId = message.attendanceId;

    if (!attendanceId && userId) {
      chrome.action.setBadgeText({ text: 'WAIT' });
      chrome.action.setBadgeBackgroundColor({ color: '#FFA500' });
      
      fetch(`${API_BASE}/get-active-attendance?user_id=${userId}&auto_start=1`)
        .then(res => res.json())
        .then(data => {
          if (data.success && data.attendance_id) {
            attendanceId = data.attendance_id;
            isCapturing = true;
            chrome.storage.local.set({ isCapturing: true, userId, attendanceId, wasCapturingBeforeClose: true });
            chrome.action.setBadgeText({ text: 'ON' });
            chrome.action.setBadgeBackgroundColor({ color: '#4CAF50' });
            startHeartbeat();
            startActivityTracking();
          } else {
            chrome.action.setBadgeText({ text: 'ERR' });
            chrome.action.setBadgeBackgroundColor({ color: '#FF0000' });
          }
        }).catch(err => {
          console.log('Auto-fetch attendance status:', err.message);
          chrome.action.setBadgeText({ text: 'ERR' });
          chrome.action.setBadgeBackgroundColor({ color: '#FF0000' });
        });

      sendResponse({ success: true });
      return false;
    }

    if (userId && attendanceId) {
      if (!isCapturing) {
        console.log('🚀 Auto-starting tracking for User:', userId, 'Attendance:', attendanceId);
        isCapturing = true;
        chrome.storage.local.set({ isCapturing: true, userId, attendanceId, wasCapturingBeforeClose: true });
      }

      chrome.action.setBadgeText({ text: 'ON' });
      chrome.action.setBadgeBackgroundColor({ color: '#4CAF50' });

      startHeartbeat();
      startActivityTracking();
    }

    sendResponse({ success: true });
    return false;
  } else if (message.action === 'stopCapture') {
    stopCapturing(true);
    sendResponse({ success: true });
    return false;
  }
  return false;
});

// Listener for tab updates
chrome.tabs.onUpdated.addListener((tabId, changeInfo, tab) => {
  if (changeInfo.status === 'complete' && tab.url && tab.url.includes('crm.devloper.space')) {
    chrome.storage.local.get(['userId', 'attendanceId', 'isCapturing'], (res) => {
      if (res.userId && res.attendanceId) {
        if (!isCapturing || !res.isCapturing) {
          console.log('🔄 Tab refresh: Resuming tracking');
          isCapturing = true;
          chrome.storage.local.set({ isCapturing: true });
          startActivityTracking();
          startHeartbeat();
        }
      }
    });
  }
});

function startHeartbeat() {
  if (heartbeatInterval) clearInterval(heartbeatInterval);
  heartbeatInterval = setInterval(sendHeartbeat, 30000);
}

async function sendHeartbeat() {
  if (!isCapturing || !userId || !attendanceId) return;
  fetch(`${API_BASE}/heartbeat`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ user_id: userId, attendance_id: attendanceId })
  }).catch(() => { });
}

function stopCapturing(isManualStop = false) {
  const uId = userId;
  isCapturing = false;
  if (heartbeatInterval) clearInterval(heartbeatInterval);
  if (activityTrackingInterval) clearInterval(activityTrackingInterval);

  chrome.action.setBadgeText({ text: 'OFF' });
  chrome.action.setBadgeBackgroundColor({ color: '#FF0000' });

  if (uId && isManualStop) {
    fetch(`${API_BASE}/clock-out`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: uId })
    }).catch(() => { });
    
    chrome.storage.local.set({ 
      isCapturing: false,
      wasCapturingBeforeClose: false
    });
  } else {
    chrome.storage.local.set({ 
      isCapturing: false,
      wasCapturingBeforeClose: true
    });
  }
}

// Activity tracking - URLs and window titles
function startActivityTracking() {
  if (activityTrackingInterval) clearInterval(activityTrackingInterval);
  
  trackActivity();
  activityTrackingInterval = setInterval(trackActivity, 60000);
}

async function trackActivity() {
  if (!isCapturing || !userId || !attendanceId) return;
  
  try {
    const tabs = await chrome.tabs.query({ active: true, currentWindow: true });
    if (tabs.length === 0) return;
    
    const activeTab = tabs[0];
    const url = activeTab.url || 'Unknown';
    const title = activeTab.title || 'Unknown';
    const timestamp = new Date().toISOString();
    
    // Only send if URL changed (avoid duplicates)
    if (url !== lastTrackedUrl) {
      lastTrackedUrl = url;
      
      fetch(`${API_BASE}/activity-track`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          user_id: userId,
          attendance_id: attendanceId,
          url: url,
          title: title,
          tracked_at: timestamp,
          type: 'url'
        })
      }).then(res => res.json())
        .then(data => {
          if (data.success) {
            console.log(`✓ Activity tracked: ${title}`);
            // Update activity count in storage
            chrome.storage.local.get(['activityCount'], (res) => {
              const count = (res.activityCount || 0) + 1;
              const time = new Date().toLocaleTimeString();
              chrome.storage.local.set({
                activityCount: count,
                lastActivity: time
              });
            });
          }
        })
        .catch(err => console.log('Activity tracking status:', err.message));
    }
  } catch (err) {
    if (err.message && !err.message.includes('target tab')) {
      console.log('Activity tracking status:', err.message);
    }
  }
}
