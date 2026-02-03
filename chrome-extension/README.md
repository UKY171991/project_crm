# CRM Screenshot Monitor - Chrome Extension

## 🎯 What This Does
This Chrome extension automatically captures your **entire screen** every minute and uploads it to the CRM system. **No permission prompts on every page refresh!**

## ✨ Features
- ✅ **One-time permission** - Grant once, works forever
- ✅ **Persists across refreshes** - No re-authorization needed
- ✅ **Runs in background** - Works even when CRM tab is closed
- ✅ **Auto-detects attendance** - Automatically finds your attendance ID
- ✅ **Entire screen capture** - Captures all applications, not just browser
- ✅ **Easy control** - Simple popup to start/stop

## 📦 Installation

### Step 1: Prepare the Extension
The extension files are already in the `chrome-extension` folder.

### Step 2: Create Extension Icons
Create three PNG images (or use placeholders):
- `chrome-extension/icons/icon16.png` (16x16 pixels)
- `chrome-extension/icons/icon48.png` (48x48 pixels)
- `chrome-extension/icons/icon128.png` (128x128 pixels)

You can use any camera/screenshot icon. For now, you can use placeholder images.

### Step 3: Load Extension in Chrome
1. Open Chrome browser
2. Go to: `chrome://extensions/`
3. Enable **"Developer mode"** (toggle in top-right)
4. Click **"Load unpacked"**
5. Select the `chrome-extension` folder from your project
6. Extension is now installed! 🎉

## 🚀 How to Use

### First Time Setup:
1. Click the extension icon in Chrome toolbar
2. Enter your **User ID** (get from CRM profile)
3. Enter your **Attendance ID** (or let it auto-detect)
4. Click **"▶️ Start Capturing"**
5. **Allow screen sharing** when prompted (ONE TIME ONLY)
6. Select **"Entire Screen"** and click **"Share"**

### That's It!
- Extension will now capture screenshots every 60 seconds
- Works even if you refresh the page
- Works even if you close the CRM tab
- Permission persists - no more prompts!

### To Stop:
1. Click the extension icon
2. Click **"⏹️ Stop Capturing"**

## 🔧 How It Works

1. **Background Service**: Runs continuously in Chrome
2. **Screen Capture**: Uses Chrome's desktopCapture API
3. **Auto Upload**: Sends screenshots to CRM API
4. **Persistent**: Saves state in Chrome storage

## 📸 What Gets Captured
- ✅ Entire desktop screen
- ✅ All open applications
- ✅ Multiple monitors (if selected)
- ✅ Taskbar and system tray
- ✅ Everything visible on screen

## 🔒 Privacy & Security
- Only captures when you click "Start"
- You control when it starts/stops
- Permission granted once, stored by Chrome
- Screenshots only uploaded to your CRM account

## 🛠️ Troubleshooting

**Extension not appearing:**
- Make sure Developer mode is enabled
- Refresh the extensions page

**"User cancelled screen sharing":**
- You need to allow screen sharing
- Click the extension icon and start again

**Screenshots not uploading:**
- Check if Laravel server is running: `php artisan serve`
- Check browser console for errors

**Permission asked again:**
- This is normal if you restart Chrome
- Just allow once and it will persist

## 📝 Configuration

### Change Capture Interval:
Edit `background.js`, line 48:
```javascript
captureInterval = setInterval(captureScreen, 60000); // 60000 = 1 minute
```

### Change Server URL:
Edit `background.js`, line 113:
```javascript
const response = await fetch('http://127.0.0.1:8000/api/screenshot-upload', {
```

## 🎨 Customization

You can customize the extension:
- Change icon images in `icons/` folder
- Modify popup UI in `popup.html`
- Adjust capture logic in `background.js`

## 🆚 Extension vs Desktop App

**Chrome Extension (Recommended):**
- ✅ No separate app to run
- ✅ Integrated with browser
- ✅ Persists permissions
- ✅ Easy to control
- ❌ Requires Chrome browser

**Desktop App (Alternative):**
- ✅ Works with any browser
- ✅ More control
- ❌ Separate process to manage
- ❌ Needs Python installed

## 📞 Support
If you have issues, check the Chrome extension console:
1. Go to `chrome://extensions/`
2. Click "Details" on CRM Screenshot Monitor
3. Click "Inspect views: service worker"
4. Check console for errors
