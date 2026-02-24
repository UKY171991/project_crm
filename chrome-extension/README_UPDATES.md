# Chrome Extension - Activity & Time Tracking Only

## What's Changed

### ✅ Removed Screenshot Functionality
- No more screenshot capture
- No permission prompts
- No screen capture needed
- Lightweight and privacy-friendly

### ✅ Activity Tracking Only
- Tracks visited URLs
- Tracks page titles
- Tracks time spent
- Automatic and silent

### ✅ Time Tracking
- Clock in/out automatically
- Tracks work hours
- Syncs with CRM
- Browser close/restart handling

### ✅ Projects Display
- Shows pending/running projects
- Regular users: See assigned projects
- Master/Admin: See all projects
- Real-time updates

### ✅ Pending Payments (Master/Admin)
- Shows all pending payments
- Displays amount and client
- Only for master/admin users

## How It Works

### On Chrome Open:
1. Extension detects user was tracking before
2. Shows "WAIT" badge (orange)
3. Auto-starts tracking (no permission needed!)
4. Shows "ON" badge (green)
5. Starts tracking URLs every 60 seconds

### During Work:
- Timer runs automatically
- URLs and page titles tracked every 60 seconds
- Projects displayed in popup
- Works completely automatically
- No user interaction needed
- No screenshots taken

### On Chrome Close:
- Timer stops
- Tracking stops
- Badge shows "OFF" (red)
- Session stays active

### On Chrome Reopen:
- Timer resumes automatically
- Tracking resumes automatically
- Same session continues
- No permission needed

## Badge Colors

- 🟢 **ON** = Tracking active
- 🔴 **OFF** = Not tracking
- 🟠 **WAIT** = Starting up
- 🔴 **ERR** = Error occurred

## What Gets Tracked

✅ Visited URLs
✅ Page titles
✅ Time spent
✅ Work hours
✅ Projects assigned

❌ NO screenshots
❌ NO screen capture
❌ NO permission prompts

## Installation

1. Reload extension: `chrome://extensions/` → Reload
2. Login to extension
3. Extension starts automatically!
4. No permissions needed!

## Daily Use

1. Open Chrome
2. Extension starts automatically
3. Work normally
4. Close Chrome when done

**That's it! No screenshots, no permissions, just time and URL tracking!**

## Database

Table: `activity_logs`
- Stores visited URLs
- Stores page titles
- Tracks timestamp
- Links to user and attendance

## API Endpoints

### Activity Tracking
- `/api/activity-track` - Track URLs and activities
- Accepts: user_id, attendance_id, url, title, tracked_at
- Returns: success/failure
- Duplicate detection (30-second window)

### Projects
- `/api/get-projects` - Get user's projects
- Accepts: user_id, role
- Returns: List of pending/running projects

### Payments
- `/api/get-pending-payments` - Get pending payments
- Returns: List of pending payments

## User Roles

### Regular User
- See assigned projects
- Track time and URLs
- No payments section

### Master/Admin User
- See ALL projects
- See ALL pending payments
- Full access

## Features Summary

✅ Automatic time tracking
✅ URL and activity tracking
✅ Browser close/restart handling
✅ Projects display (role-based)
✅ Pending payments (master/admin)
✅ Real-time status badges
✅ Duplicate prevention
✅ Accurate timestamps
✅ NO screenshots
✅ NO permissions needed
✅ Privacy-friendly

## Privacy

- Only tracks URLs and page titles
- No screenshots taken
- No screen capture
- No keyboard/mouse tracking
- No webcam/audio
- Minimal data collection
- Privacy-friendly approach
