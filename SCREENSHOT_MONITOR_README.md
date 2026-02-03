# CRM Screenshot Monitor - Setup Instructions

## What This Does
This desktop application automatically captures your **entire screen** every minute and uploads it to the CRM system. No browser permissions needed!

## Features
- ✅ Captures entire screen (all monitors)
- ✅ Runs in background
- ✅ No browser permission prompts
- ✅ Automatic upload to CRM
- ✅ Works even when browser is minimized

## Installation

### Step 1: Install Python
1. Download Python from: https://www.python.org/downloads/
2. Install Python (make sure to check "Add Python to PATH")

### Step 2: Install Dependencies
Open Command Prompt in the project folder and run:
```bash
pip install -r requirements.txt
```

### Step 3: Get Your User ID
1. Log in to the CRM
2. Go to your profile or ask admin for your User ID

## How to Use

### Start the Monitor
1. Open Command Prompt
2. Navigate to project folder:
   ```bash
   cd C:\git\project_crm
   ```
3. Run the monitor:
   ```bash
   python screenshot_monitor.py
   ```
4. Enter your User ID when prompted
5. Keep the window open (minimize it if needed)

### Stop the Monitor
Press `Ctrl+C` in the Command Prompt window

## What Gets Captured
- ✅ Entire desktop
- ✅ All open applications
- ✅ Multiple monitors (if you have them)
- ✅ Taskbar and system tray
- ✅ Everything visible on screen

## Frequency
- Screenshots are taken every **60 seconds** (1 minute)
- You can change this in `screenshot_monitor.py` (line 10)

## Privacy Notes
- Screenshots are only taken while the monitor is running
- You can stop it anytime with Ctrl+C
- Screenshots are uploaded to your CRM account
- Only captures when you're clocked in

## Troubleshooting

**"Module not found" error:**
```bash
pip install pillow requests
```

**"Connection refused" error:**
- Make sure Laravel server is running: `php artisan serve`

**Screenshots not appearing:**
- Check if you're clocked in to the CRM
- Check the console for error messages

## Running on Startup (Optional)

To run automatically when Windows starts:
1. Press `Win+R`
2. Type `shell:startup` and press Enter
3. Create a shortcut to `run_monitor.bat` in this folder
4. The monitor will start automatically on login

## Support
If you have issues, check the console output for error messages.
