// Offscreen script
let activeStream = null;
let captureInterval = null;
let isInitializing = false;

console.log('Offscreen document loaded');

// Signal ready
chrome.runtime.sendMessage({ action: 'offscreenReady' });

chrome.runtime.onMessage.addListener(async (message) => {
    if (message.action === 'initCapture') {
        if (isInitializing) return;
        isInitializing = true;

        const { streamId, userId, attendanceId } = message;
        console.log('Offscreen: Activating Stream ID', streamId);

        try {
            // Cleanup existing
            stopEverything();

            // Wait brief moment for hardware release
            await new Promise(r => setTimeout(r, 200));

            // GET MEDIA
            activeStream = await navigator.mediaDevices.getUserMedia({
                audio: false,
                video: {
                    mandatory: {
                        chromeMediaSource: 'desktop',
                        chromeMediaSourceId: streamId
                    }
                }
            });

            console.log('🎬 Offscreen: Stream ACTIVE');

            if (captureInterval) clearInterval(captureInterval);

            // First capture stabilization
            setTimeout(() => takeScreenshot(userId, attendanceId), 3000);

            captureInterval = setInterval(() => {
                takeScreenshot(userId, attendanceId);
            }, 60000);

        } catch (err) {
            console.error('Offscreen Hardware Error:', err.name, err.message);
            chrome.runtime.sendMessage({
                action: 'captureError',
                error: `${err.name}: ${err.message}`
            });
            stopEverything();
        } finally {
            isInitializing = false;
        }
    } else if (message.action === 'stopOffscreenCapture') {
        stopEverything();
    }
});

async function takeScreenshot(userId, attendanceId) {
    if (!activeStream || !activeStream.active) return;

    try {
        const video = document.createElement('video');
        video.srcObject = activeStream;
        video.muted = true;

        await new Promise((resolve, reject) => {
            video.onloadedmetadata = () => {
                video.play().then(resolve).catch(reject);
            };
            video.onerror = reject;
            setTimeout(() => reject(new Error('Buffer Delay')), 10000);
        });

        // Frame rendering stabilization
        await new Promise(r => setTimeout(r, 1000));

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);

        const base64image = canvas.toDataURL('image/jpeg', 0.8);

        if (base64image.length > 5000) {
            chrome.runtime.sendMessage({
                action: 'screenshotCaptured',
                imageData: base64image,
                userId,
                attendanceId
            });
        }

        video.srcObject = null;
        video.remove();
    } catch (err) {
        console.warn('Snapshot skipped:', err.message);
    }
}

function stopEverything() {
    console.log('Offscreen: Internal Cleanup');
    if (captureInterval) {
        clearInterval(captureInterval);
        captureInterval = null;
    }
    if (activeStream) {
        activeStream.getTracks().forEach(track => {
            track.stop();
            console.log('Track Stopped');
        });
        activeStream = null;
    }
}
