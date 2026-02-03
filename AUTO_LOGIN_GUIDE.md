# AUTO-LOGIN SCREENSHOT MONITOR

## ✨ The Ultimate Solution - Fully Automatic!

This version **automatically logs in** to your CRM and detects your User ID and Attendance ID!

## Features

- ✅ **Auto-login** - Just enter email and password once
- ✅ **Auto-detect User ID** - No manual entry needed
- ✅ **Auto-detect Attendance ID** - Finds your clock-in automatically
- ✅ **No browser dialogs** - Completely silent
- ✅ **Entire screen capture** - All applications
- ✅ **Auto-upload** - Sends to CRM automatically

## Quick Start

### Step 1: Install Dependencies (One Time)
```bash
pip install pillow requests
```

### Step 2: Run the Monitor
**Option A: Double-click**
- Double-click `run_auto_monitor.bat`

**Option B: Command Line**
```bash
python screenshot_monitor_auto.py
```

### Step 3: Enter Credentials
```
Email: your.email@example.com
Password: your_password
```

### Step 4: Done!
The app will:
1. ✓ Login to CRM
2. ✓ Detect your User ID automatically
3. ✓ Detect your Attendance ID automatically
4. ✓ Start capturing screenshots every 60 seconds

## What You'll See

```
==================================================================
  CRM SCREENSHOT MONITOR - AUTO-LOGIN VERSION
  Automatically detects User ID and Attendance ID
==================================================================

Please enter your CRM login credentials:
Email: umakant@example.com
Password: ********

🔐 Logging in to CRM...
✓ Login successful!
✓ User ID detected: 1

✓ User ID: 1

Checking if you're clocked in...
✓ Active attendance found (ID: 5)

==================================================================
  MONITORING STARTED
==================================================================
  Capturing every 60 seconds
  Press Ctrl+C to stop
==================================================================

[19:38:00] 📸 Screenshot #1... (245.6 KB) ✓ Screenshot uploaded (ID: 123)
[19:39:00] 📸 Screenshot #2... (248.2 KB) ✓ Screenshot uploaded (ID: 124)
[19:40:00] 📸 Screenshot #3... (251.1 KB) ✓ Screenshot uploaded (ID: 125)
```

## How It Works

1. **Login**: Uses your email/password to login to CRM
2. **Session**: Maintains login session with cookies
3. **User Detection**: Automatically gets your User ID from the session
4. **Attendance Detection**: Checks if you're clocked in
5. **Auto-Start**: Begins capturing immediately
6. **Auto-Refresh**: Updates attendance ID if you clock in/out

## Advantages Over Other Methods

### vs Browser Extension
- ✅ No browser dialogs
- ✅ No screen sharing prompts
- ✅ Completely invisible

### vs Manual Desktop App
- ✅ No need to enter User ID
- ✅ No need to enter Attendance ID
- ✅ Fully automatic

### vs Silent Monitor
- ✅ Auto-detects everything
- ✅ Waits for clock-in automatically
- ✅ Updates attendance automatically

## Special Features

### Auto Clock-In Detection
If you're not clocked in when you start the app:
```
⚠ No active attendance. Please clock in first.

Waiting for you to clock in...
.....
✓ Clocked in! Attendance ID: 5
```

### Auto Attendance Refresh
The app checks for attendance changes every 5 screenshots:
```
✓ Attendance updated (ID: 6)
```

### Session Management
Maintains your login session throughout the monitoring period.

## To Stop

Press `Ctrl+C` in the command window

## Security Notes

- Your password is only used for initial login
- Session is maintained with cookies
- No passwords are stored
- All communication is with your CRM server only

## Troubleshooting

**"Login failed":**
- Check your email and password
- Make sure CRM server is running
- Try logging in via browser first

**"Could not auto-detect User ID":**
- The app will ask you to enter it manually
- This is a fallback if auto-detection fails

**"No active attendance":**
- Make sure you're clocked in to the CRM
- The app will wait for you to clock in

**"Connection refused":**
- Make sure Laravel server is running: `php artisan serve`

## Configuration

Edit `screenshot_monitor_auto.py` to change:
- `INTERVAL_SECONDS = 60` - Change capture interval
- `CRM_URL = "http://127.0.0.1:8000"` - Change server URL

## Run on Startup

To start automatically when Windows boots:
1. Press `Win+R`
2. Type `shell:startup` and press Enter
3. Create shortcut to `run_auto_monitor.bat`
4. Restart Windows

**Note**: You'll need to enter credentials each time unless you modify the script to save them (not recommended for security).

---

**This is the MOST AUTOMATED solution available!**

Just enter your email and password, and everything else is automatic! 🚀
