// Background service worker
let isCapturing = false;
let userId = null;
let attendanceId = null;
let heartbeatInterval = null;
let crmTabId = null;

const API_BASE = 'https://crm.devloper.space/api';

// Initial state sync
chrome.storage.local.get(['isCapturing', 'userId', 'attendanceId'], (result) => {
  isCapturing = result.isCapturing || false;
  userId = result.userId || null;
  attendanceId = result.attendanceId || null;

  if (isCapturing) {
    chrome.action.setBadgeText({ text: 'ON' });
    chrome.action.setBadgeBackgroundColor({ color: '#F44336' });
    // Screenshot capture disabled per user request
    // ensureOffscreenReady();
    startHeartbeat();
  }
});

chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
  if (message.action === 'ensureOffscreen') {
    // Screenshot capture disabled
    sendResponse({ success: true });
    return true;
  } else if (message.action === 'startCapture' || message.action === 'crmPageOpened') {
    if (isCapturing && userId === message.userId && attendanceId === message.attendanceId) {
      if (sender.tab) crmTabId = sender.tab.id;
      sendResponse({ success: true });
      return true;
    }

    userId = message.userId;
    attendanceId = message.attendanceId;
    isCapturing = true;

    if (sender.tab) {
      crmTabId = sender.tab.id;
    }

    chrome.storage.local.set({ isCapturing: true, userId, attendanceId });
    chrome.action.setBadgeText({ text: 'ON' });
    chrome.action.setBadgeBackgroundColor({ color: '#F44336' });

    // ONLY HEARTBEAT (TIME RECORDING) - SCREENSHOT HELD
    startHeartbeat();

    sendResponse({ success: true });
  } else if (message.action === 'stopCapture') {
    stopCapturing();
    sendResponse({ success: true });
  } else if (message.action === 'captureError') {
    console.error('FATAL Capture Error:', message.error);
    isCapturing = false;
    chrome.storage.local.set({ isCapturing: false });
    chrome.action.setBadgeText({ text: '' });
    if (heartbeatInterval) clearInterval(heartbeatInterval);
    chrome.runtime.sendMessage({ action: 'stopOffscreenCapture' }).catch(() => { });
  } else if (message.action === 'screenshotCaptured') {
    // uploadScreenshot(message.imageData, message.userId, message.attendanceId);
  }
  return true;
});

// Listener for tab closure
chrome.tabs.onRemoved.addListener((tabId, removeInfo) => {
  if (tabId === crmTabId) {
    // Check if there are other CRM tabs open
    chrome.tabs.query({ url: "https://crm.devloper.space/*" }, (tabs) => {
      if (tabs && tabs.length > 0) {
        // Switch to another open CRM tab
        crmTabId = tabs[0].id;
        console.log('Switched tracking to another CRM tab:', crmTabId);
      } else {
        // No CRM tabs left, stop capturing
        stopCapturing();
        crmTabId = null;
      }
    });
  }
});


async function ensureOffscreenReady() {
  // Disabled to stop screenshots
  return;
}

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

function stopCapturing() {
  const uId = userId;
  isCapturing = false;
  if (heartbeatInterval) clearInterval(heartbeatInterval);

  if (uId) {
    fetch(`${API_BASE}/clock-out`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: uId })
    }).catch(() => { });
  }

  chrome.storage.local.set({ isCapturing: false });
  chrome.action.setBadgeText({ text: '' });
  chrome.runtime.sendMessage({ action: 'stopOffscreenCapture' }).catch(() => { });
  chrome.offscreen.closeDocument().catch(() => { });
}

async function uploadScreenshot(imageData, uId, aId) {
  // Disabled
  return;
}
