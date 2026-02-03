# Quick Installation Guide - AUTO-START VERSION

## ✨ NEW: Automatic Screenshot Capture!

This extension now **automatically starts** when you open the CRM website!

## Install Chrome Extension (5 Minutes)

### Step 1: Open Chrome Extensions
1. Open Google Chrome
2. Type in address bar: `chrome://extensions/`
3. Press Enter

### Step 2: Enable Developer Mode
1. Look for "Developer mode" toggle in top-right corner
2. Click to enable it (should turn blue)

### Step 3: Load Extension
1. Click "Load unpacked" button
2. Navigate to: `C:\git\project_crm\chrome-extension`
3. Click "Select Folder"
4. Extension is now installed! ✅

### Step 4: First Time Setup (ONE TIME ONLY)
1. Open your CRM website: `http://127.0.0.1:8000`
2. Log in to your account
3. Extension will **automatically detect** your User ID and Attendance ID
4. You'll see a **screen sharing prompt** (ONE TIME ONLY)
5. Select "**Entire Screen**" and click "**Share**"
6. Done! ✅

## 🎉 That's It!

From now on:
- ✅ Extension **auto-starts** when you open CRM
- ✅ **No more permission prompts** on page refresh
- ✅ Screenshots taken **every 60 seconds** automatically
- ✅ Works even if you **close the CRM tab**
- ✅ Uploads to server **automatically**

## 📸 How It Works

1. **You open CRM** → Extension detects it
2. **Auto-detects** your User ID and Attendance ID
3. **Starts capturing** automatically (if permission granted)
4. **First time only**: Asks for screen sharing permission
5. **After that**: Works silently in background

## 🛑 To Stop Capturing

1. Click the extension icon in Chrome toolbar
2. Click "⏹️ Stop Capturing"

## 🔄 To Start Again

Just open the CRM website - it auto-starts!

Or click the extension icon and click "▶️ Start Capturing"

## 📋 What You'll See

**In Browser Console (F12):**
```
CRM page loaded, detecting user and attendance...
✓ User ID: 1 Attendance ID: 5
Auto-starting screenshot capture...
📸 Capturing screenshot...
✓ Screenshot captured, size: 245.67 KB
✓ Screenshot uploaded successfully, ID: 123
```

**Chrome Notification (First Time):**
- If you cancel screen sharing, you'll see a notification
- Just click the extension icon and allow it

## 🔧 Troubleshooting

**Extension not auto-starting:**
- Make sure you're logged into CRM
- Check browser console for errors
- Click extension icon to see status

**Permission asked every time:**
- This means permission wasn't saved
- Make sure to select "Entire Screen" (not Window or Tab)
- Check if extension has all permissions

**Screenshots not uploading:**
- Check if Laravel server is running: `php artisan serve`
- Check Screenshots page in CRM

## 🎯 Benefits Over Manual Start

**Before (Manual):**
- ❌ Click extension icon every time
- ❌ Enter User ID and Attendance ID
- ❌ Click Start button
- ❌ Repeat after every refresh

**Now (Automatic):**
- ✅ Just open CRM website
- ✅ Everything happens automatically
- ✅ Permission saved forever
- ✅ Zero manual intervention

Enjoy automatic screenshot monitoring! 🎉
