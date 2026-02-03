// Offscreen script to handle screen capture
chrome.runtime.onMessage.addListener(async (message) => {
    if (message.action === 'captureScreenNow') {
        const streamId = message.streamId;
        const userId = message.userId;
        const attendanceId = message.attendanceId;

        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                audio: false,
                video: {
                    mandatory: {
                        chromeMediaSource: 'desktop',
                        chromeMediaSourceId: streamId
                    }
                }
            });

            // Create hidden video and canvas elements
            const video = document.createElement('video');
            video.srcObject = stream;
            video.play();

            await new Promise(resolve => {
                video.onloadedmetadata = resolve;
            });

            const canvas = document.createElement('canvas');
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

            const base64image = canvas.toDataURL('image/jpeg', 0.7);

            // Clean up
            stream.getTracks().forEach(track => track.stop());
            video.srcObject = null;

            // Send back to background
            chrome.runtime.sendMessage({
                action: 'screenshotCaptured',
                imageData: base64image,
                userId,
                attendanceId
            });

        } catch (err) {
            console.error('Offscreen capture failed:', err);
        }
    }
});
