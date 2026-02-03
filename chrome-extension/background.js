// Background service worker for screenshot capture
let captureInterval = null;
let isCapturing = false;
let streamId = null;
let userId = null;
let attendanceId = null;
let permissionGranted = false;

// Listen for messages from popup and content script
chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
  if (request.action === 'startCapture') {
    userId = request.userId;
    attendanceId = request.attendanceId;
    startScreenCapture();
    sendResponse({ success: true });
  } else if (request.action === 'stopCapture') {
    stopScreenCapture();
    sendResponse({ success: true });
  } else if (request.action === 'getStatus') {
    sendResponse({
      isCapturing: isCapturing,
      userId: userId,
      attendanceId: attendanceId,
      permissionGranted: permissionGranted
    });
  } else if (request.action === 'updateAttendance') {
    attendanceId = request.attendanceId;
    chrome.storage.local.set({ attendanceId: attendanceId });
    sendResponse({ success: true });
  } else if (request.action === 'updateUserId') {
    userId = request.userId;
    chrome.storage.local.set({ userId: userId });
    sendResponse({ success: true });
  } else if (request.action === 'crmPageOpened') {
    // Auto-start when CRM page is opened
    handleCRMPageOpened(request.userId, request.attendanceId);
    sendResponse({ success: true });
  }
  return true;
});

// Handle CRM page opened - auto-start capture
async function handleCRMPageOpened(pageUserId, pageAttendanceId) {
  console.log('CRM page opened, checking auto-start...');

  // Load saved settings
  const settings = await chrome.storage.local.get(['userId', 'attendanceId', 'autoStart', 'permissionGranted']);

  // Update user ID and attendance ID if provided
  if (pageUserId) {
    userId = pageUserId;
    chrome.storage.local.set({ userId: pageUserId });
  } else if (settings.userId) {
    userId = settings.userId;
  }

  if (pageAttendanceId) {
    attendanceId = pageAttendanceId;
    chrome.storage.local.set({ attendanceId: pageAttendanceId });
  } else if (settings.attendanceId) {
    attendanceId = settings.attendanceId;
  }

  // Auto-start if enabled and not already capturing
  if (settings.autoStart !== false && !isCapturing && userId && attendanceId) {
    console.log('Auto-starting screenshot capture...');
    permissionGranted = settings.permissionGranted || false;
    startScreenCapture();
  }
}

// Start screen capture
function startScreenCapture() {
  if (isCapturing) {
    console.log('Already capturing');
    return;
  }

  console.log('Starting screen capture...');
  isCapturing = true;

  // Save state
  chrome.storage.local.set({
    isCapturing: true,
    userId: userId,
    attendanceId: attendanceId,
    autoStart: true
  });

  // Show "REC" badge on extension icon
  chrome.action.setBadgeText({ text: 'ON' });
  chrome.action.setBadgeBackgroundColor({ color: '#F44336' }); // Red color

  // Capture immediately
  captureScreen();

  // Then capture every 60 seconds
  captureInterval = setInterval(captureScreen, 60000);
}

// Stop screen capture
function stopScreenCapture() {
  console.log('Stopping screen capture...');
  isCapturing = false;

  if (captureInterval) {
    clearInterval(captureInterval);
    captureInterval = null;
  }

  chrome.storage.local.set({ isCapturing: false });

  // Clear badge
  chrome.action.setBadgeText({ text: '' });
}

// Capture screen function
async function captureScreen() {
  if (!userId || !attendanceId) {
    console.log('Missing user ID or attendance ID, skipping capture');
    return;
  }

  try {
    console.log('📸 Capturing screenshot...');

    // Request screen capture permission if not granted
    chrome.desktopCapture.chooseDesktopMedia(
      ['screen', 'window'],
      async (streamId) => {
        if (!streamId) {
          console.error('Screen sharing cancelled or denied');

          // If this is first time, mark permission as not granted
          if (!permissionGranted) {
            chrome.storage.local.set({ permissionGranted: false });
            // Show notification
            chrome.notifications.create({
              type: 'basic',
              iconUrl: 'icons/icon48.png',
              title: 'CRM Screenshot Monitor',
              message: 'Please allow screen sharing to enable automatic screenshots.',
              priority: 2
            });
          }
          return;
        }

        // Mark permission as granted
        if (!permissionGranted) {
          permissionGranted = true;
          chrome.storage.local.set({ permissionGranted: true });
          console.log('✓ Screen sharing permission granted and saved!');
        }

        try {
          // Get media stream
          const stream = await navigator.mediaDevices.getUserMedia({
            audio: false,
            video: {
              mandatory: {
                chromeMediaSource: 'desktop',
                chromeMediaSourceId: streamId
              }
            }
          });

          // Create video element
          const video = document.createElement('video');
          video.srcObject = stream;
          video.play();

          // Wait for video to load
          await new Promise(resolve => {
            video.onloadedmetadata = resolve;
          });

          // Create canvas and capture frame
          const canvas = document.createElement('canvas');

          // Image compression logic
          const maxWidth = 1920;
          const maxHeight = 1080;
          let width = video.videoWidth;
          let height = video.videoHeight;

          if (width > maxWidth || height > maxHeight) {
            const ratio = Math.min(maxWidth / width, maxHeight / height);
            width = width * ratio;
            height = height * ratio;
          }

          canvas.width = width;
          canvas.height = height;

          const ctx = canvas.getContext('2d');
          ctx.drawImage(video, 0, 0, width, height);

          // Convert to JPEG with compression (70% quality)
          const base64image = canvas.toDataURL('image/jpeg', 0.7);

          console.log('✓ Screenshot captured, size:', (base64image.length / 1024).toFixed(2), 'KB');

          // Stop stream
          stream.getTracks().forEach(track => track.stop());

          // Upload to server
          await uploadScreenshot(base64image);

        } catch (err) {
          console.error('Error capturing screen:', err);
        }
      }
    );
  } catch (err) {
    console.error('Screenshot failed:', err);
  }
}

// Upload screenshot to server
async function uploadScreenshot(base64image) {
  try {
    const response = await fetch('https://crm.devloper.space/api/screenshot-upload', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        user_id: userId,
        attendance_id: attendanceId,
        image_data: base64image
      })
    });

    if (response.ok) {
      const result = await response.json();
      console.log('✓ Screenshot uploaded successfully, ID:', result.screenshot_id);

      // Update statistics
      chrome.storage.local.get(['screenshotCount'], (data) => {
        const count = (data.screenshotCount || 0) + 1;
        const now = new Date();
        const timeStr = now.toLocaleTimeString();

        chrome.storage.local.set({
          screenshotCount: count,
          lastCapture: timeStr
        });
      });
    } else {
      console.error('Upload failed:', response.status);
    }
  } catch (err) {
    console.error('Upload error:', err);
  }
}


// Restore state on startup
chrome.runtime.onStartup.addListener(async () => {
  const settings = await chrome.storage.local.get(['isCapturing', 'userId', 'attendanceId', 'autoStart', 'permissionGranted']);

  if (settings.autoStart && settings.userId && settings.attendanceId) {
    userId = settings.userId;
    attendanceId = settings.attendanceId;
    permissionGranted = settings.permissionGranted || false;

    if (settings.isCapturing) {
      startScreenCapture();
    } else {
      chrome.action.setBadgeText({ text: '' });
    }
  }
});

// Listen for when extension is installed
chrome.runtime.onInstalled.addListener((details) => {
  if (details.reason === 'install') {
    console.log('Extension installed! Auto-start enabled by default.');
    chrome.storage.local.set({ autoStart: true });
  }
});

console.log('CRM Screenshot Monitor - Background service loaded');

