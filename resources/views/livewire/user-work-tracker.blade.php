<div class="d-flex align-items-center">
    <!-- Live Clock -->
    <div class="mr-3 text-right d-none d-sm-block" wire:ignore>
        <div class="text-xs text-muted text-uppercase font-weight-bold" style="line-height: 1;">Current Time</div>
        <div id="live-clock" class="text-primary font-weight-bold" style="font-size: 1.1rem; line-height: 1;">--:--:--</div>
    </div>
    
    <div class="mr-2 text-right d-none d-sm-block">
        <div class="text-xs text-muted text-uppercase font-weight-bold" style="line-height: 1;">Work Time (Net)</div>
        <div class="text-success font-weight-bold" style="font-size: 1.1rem; line-height: 1;">{{ $workDisplay }}</div>
    </div>
    
    <div class="text-xs p-1 px-2 bg-light border rounded shadow-sm">
        <span class="text-muted">Idle: {{ $idleDisplay }}</span>
    </div>

    <div wire:poll.1000ms="recordActivity"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        // Update live clock every second
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const timeString = `${hours}:${minutes}:${seconds}`;
            
            const clockElement = document.getElementById('live-clock');
            if (clockElement) {
                clockElement.textContent = timeString;
            }
        }
        
        // Update immediately and then every second
        updateClock();
        setInterval(updateClock, 1000);
        
        document.addEventListener('livewire:initialized', () => {
            let lastActivity = Date.now();
            let idleThreshold = 60000; // 1 minute for testing/demo, adjust to 5 mins (300000) for prod
            let screenshotInterval = 60000; // 1 minute for testing (change to 300000 for 5 mins in production)
            
            console.log('Work tracker initialized. Screenshot interval:', screenshotInterval / 1000, 'seconds');
            
            // Track activity
            ['mousemove', 'keydown', 'click', 'scroll'].forEach(evt => {
                document.addEventListener(evt, () => {
                    lastActivity = Date.now();
                });
            });

            // Idle Detection Loop
            setInterval(() => {
                let now = Date.now();
                if (now - lastActivity > idleThreshold) {
                    // Send 10s idle increment every 10s if inactive
                    @this.recordActivity(10);
                }
            }, 10000);

            // Screenshot Loop - Capture entire screen using Screen Capture API
            let screenshotCount = 0;
            let mediaStream = null;
            let permissionGranted = false;
            
            // Request screen capture permission ONCE
            async function initScreenCapture() {
                if (permissionGranted && mediaStream) {
                    return true; // Already have permission
                }
                
                try {
                    console.log('Requesting screen capture permission (one-time)...');
                    mediaStream = await navigator.mediaDevices.getDisplayMedia({
                        video: {
                            displaySurface: 'monitor', // Capture entire screen
                            cursor: 'always'
                        },
                        audio: false
                    });
                    
                    console.log('✓ Screen capture permission granted! Will continue automatically.');
                    permissionGranted = true;
                    
                    // Listen for when user stops sharing
                    mediaStream.getVideoTracks()[0].addEventListener('ended', () => {
                        console.log('Screen sharing stopped by user');
                        permissionGranted = false;
                        mediaStream = null;
                    });
                    
                    return true;
                } catch (err) {
                    console.error('Screen capture permission denied:', err);
                    permissionGranted = false;
                    return false;
                }
            }
            
            // Capture screenshot from stream
            async function captureScreen() {
                const attendanceId = @this.currentAttendanceId;
                
                if (!attendanceId) {
                    console.log('Not clocked in, skipping screenshot');
                    return;
                }
                
                // Request permission only if we don't have it yet
                if (!permissionGranted || !mediaStream) {
                    const granted = await initScreenCapture();
                    if (!granted) {
                        console.log('Waiting for screen sharing permission...');
                        return;
                    }
                }
                
                try {
                    console.log('📸 Taking screenshot #' + (++screenshotCount) + '...');
                    
                    const video = document.createElement('video');
                    video.srcObject = mediaStream;
                    video.play();
                    
                    // Wait for video to be ready
                    await new Promise((resolve, reject) => {
                        video.onloadedmetadata = resolve;
                        setTimeout(() => reject('Video timeout'), 5000);
                    });
                    
                    const canvas = document.createElement('canvas');
                    // Reduce resolution to decrease file size
                    const maxWidth = 1920;
                    const maxHeight = 1080;
                    let width = video.videoWidth;
                    let height = video.videoHeight;
                    
                    // Scale down if too large
                    if (width > maxWidth || height > maxHeight) {
                        const ratio = Math.min(maxWidth / width, maxHeight / height);
                        width = width * ratio;
                        height = height * ratio;
                    }
                    
                    canvas.width = width;
                    canvas.height = height;
                    
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, width, height);
                    
                    // Use JPEG with compression instead of PNG to reduce size
                    const base64image = canvas.toDataURL('image/jpeg', 0.7); // 70% quality
                    console.log('✓ Screenshot captured, size:', (base64image.length / 1024).toFixed(2), 'KB');
                    console.log('⬆ Uploading to server...');
                    
                    @this.uploadScreenshot(base64image);
                    
                    // Clean up
                    video.pause();
                    video.srcObject = null;
                    
                } catch (err) {
                    console.error('Screenshot capture failed:', err);
                    
                    // If stream was stopped, reset permission
                    if (err.name === 'InvalidStateError' || err.message === 'Video timeout') {
                        console.log('Stream may have been stopped, will request permission on next capture');
                        permissionGranted = false;
                        mediaStream = null;
                    }
                }
            }
            
            /*
            // Request permission immediately when page loads
            console.log('🎬 Initializing screen capture...');
            initScreenCapture().then(granted => {
                if (granted) {
                    console.log('✓ Ready! Screenshots will be taken every', screenshotInterval / 1000, 'seconds');
                } else {
                    console.log('⚠ Please refresh and allow screen sharing to enable screenshots');
                }
            });
            
            // Take screenshots at interval
            setInterval(captureScreen, screenshotInterval);
            */
        });
    </script>
</div>
