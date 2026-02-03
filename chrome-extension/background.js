// Background service worker
let isCapturing = false;
let userId = null;
let attendanceId = null;

const API_URL = 'https://crm.devloper.space/api/screenshot-upload';

// Initial state sync
chrome.storage.local.get(['isCapturing', 'userId', 'attendanceId'], (result) => {
  isCapturing = result.isCapturing || false;
  userId = result.userId || null;
  attendanceId = result.attendanceId || null;

  if (isCapturing) {
    chrome.action.setBadgeText({ text: 'ON' });
    chrome.action.setBadgeBackgroundColor({ color: '#F44336' });
    setupOffscreen();
  }
});

chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
  if (message.action === 'ensureOffscreen') {
    setupOffscreen().then(() => sendResponse({ success: true }));
    return true;
  } else if (message.action === 'startCapture') {
    userId = message.userId;
    attendanceId = message.attendanceId;
    isCapturing = true;

    chrome.storage.local.set({ isCapturing: true, userId, attendanceId });
    chrome.action.setBadgeText({ text: 'ON' });
    chrome.action.setBadgeBackgroundColor({ color: '#F44336' });

    // Forward IMMEDIATELY to offscreen
    chrome.runtime.sendMessage({
      action: 'initCapture',
      streamId: message.streamId,
      userId: userId,
      attendanceId: attendanceId
    }).catch(err => {
      // If failed, offscreen might be starting, retry once in 500ms
      setTimeout(() => {
        chrome.runtime.sendMessage({
          action: 'initCapture',
          streamId: message.streamId,
          userId: userId,
          attendanceId: attendanceId
        });
      }, 500);
    });

    sendResponse({ success: true });
  } else if (message.action === 'stopCapture') {
    stopCapturing();
    sendResponse({ success: true });
  } else if (message.action === 'screenshotCaptured') {
    uploadScreenshot(message.imageData, message.userId, message.attendanceId);
  }
  return true;
});

async function setupOffscreen() {
  const contexts = await chrome.runtime.getContexts({ contextTypes: ['OFFSCREEN_DOCUMENT'] });
  if (contexts.length > 0) return;

  await chrome.offscreen.createDocument({
    url: 'offscreen.html',
    reasons: ['DISPLAY_MEDIA'],
    justification: 'Work monitoring'
  });
}

function stopCapturing() {
  isCapturing = false;
  chrome.storage.local.set({ isCapturing: false });
  chrome.action.setBadgeText({ text: '' });
  chrome.runtime.sendMessage({ action: 'stopOffscreenCapture' }).catch(() => { });
  chrome.offscreen.closeDocument().catch(() => { });
}

async function uploadScreenshot(imageData, uId, aId) {
  if (!uId || !aId) return;
  try {
    await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: uId, attendance_id: aId, image_data: imageData })
    });

    const timeStr = new Date().toLocaleTimeString();
    chrome.storage.local.get(['screenshotCount'], (res) => {
      const count = (res.screenshotCount || 0) + 1;
      chrome.storage.local.set({ screenshotCount: count, lastCapture: timeStr });
    });
  } catch (err) {
    console.error('Upload failed:', err);
  }
}
