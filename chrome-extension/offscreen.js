// Offscreen script
let activeStream = null;
let captureInterval = null;

console.log('Offscreen document initialized');

// Signal ready immediately
chrome.runtime.sendMessage({ action: 'offscreenReady' });

chrome.runtime.onMessage.addListener(async (message) => {
    if (message.action === 'initCapture') {
        const { streamId, userId, attendanceId } = message;
        console.log('Offscreen: Received initCapture command');

        if (!streamId) {
            console.error('Offscreen: No streamId received');
            return;
        }

        try {
            // Stop logic
            if (activeStream) {
                activeStream.getTracks().forEach(t => t.stop());
                activeStream = null;
            }

            // GET MEDIA with exact same constraints as the picker context
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

            // First capture
            setTimeout(() => takeScreenshot(userId, attendanceId), 1000);

            captureInterval = setInterval(() => {
                takeScreenshot(userId, attendanceId);
            }, 60000);

        } catch (err) {
            console.error('Offscreen FATAL:', err.name, err.message);
            chrome.runtime.sendMessage({
                action: 'captureError',
                error: `${err.name}: ${err.message}`
            });
        }
    } else if (message.action === 'stopOffscreenCapture') {
        stopEverything();
    }
});

async function takeScreenshot(userId, attendanceId) {
    if (!activeStream || !activeStream.active) {
        console.log('Offscreen: Stream lost');
        return;
    }

    try {
        const video = document.createElement('video');
        video.srcObject = activeStream;
        video.muted = true;

        await new Promise((resolve, reject) => {
            video.onloadedmetadata = () => {
                video.play().then(resolve).catch(reject);
            };
            video.onerror = reject;
            // Timeout safety
            setTimeout(() => reject(new Error('Video load timeout')), 5000);
        });

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);

        const base64image = canvas.toDataURL('image/jpeg', 0.7);

        chrome.runtime.sendMessage({
            action: 'screenshotCaptured',
            imageData: base64image,
            userId,
            attendanceId
        });

        video.srcObject = null;
        video.remove();
    } catch (err) {
        console.error('Offscreen: Frame failed:', err);
    }
}

function stopEverything() {
    if (captureInterval) clearInterval(captureInterval);
    if (activeStream) {
        activeStream.getTracks().forEach(track => track.stop());
        activeStream = null;
    }
}
