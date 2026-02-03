import time
import requests
import base64
from PIL import ImageGrab
from datetime import datetime
import os
import sys

# Configuration
API_URL = "http://127.0.0.1:8000/api/screenshot-upload"
INTERVAL_SECONDS = 60  # 1 minute
USER_ID = None
ATTENDANCE_ID = None

def capture_screenshot():
    """Capture the entire screen - NO DIALOGS, NO PROMPTS"""
    try:
        # Capture entire screen silently
        screenshot = ImageGrab.grab(all_screens=True)
        
        # Convert to base64
        from io import BytesIO
        buffer = BytesIO()
        screenshot.save(buffer, format='PNG')
        image_data = base64.b64encode(buffer.getvalue()).decode('utf-8')
        
        return f"data:image/png;base64,{image_data}"
    except Exception as e:
        print(f"Error capturing screenshot: {e}")
        return None

def upload_screenshot(image_data):
    """Upload screenshot to server"""
    try:
        payload = {
            "user_id": USER_ID,
            "attendance_id": ATTENDANCE_ID,
            "image_data": image_data
        }
        
        response = requests.post(API_URL, json=payload, timeout=10)
        
        if response.status_code == 200:
            result = response.json()
            print(f"✓ Screenshot uploaded successfully (ID: {result.get('screenshot_id', 'N/A')})")
            return True
        else:
            print(f"✗ Upload failed: {response.status_code}")
            return False
            
    except Exception as e:
        print(f"✗ Upload error: {e}")
        return False

def get_active_attendance():
    """Get active attendance ID from server"""
    try:
        response = requests.post(
            'http://127.0.0.1:8000/api/get-active-attendance',
            json={'user_id': USER_ID},
            timeout=5
        )
        
        if response.status_code == 200:
            data = response.json()
            if data.get('clocked_in'):
                return data.get('attendance_id')
        return None
    except:
        return None

def main():
    global USER_ID, ATTENDANCE_ID
    
    print("=" * 60)
    print("  CRM SCREENSHOT MONITOR - SILENT MODE")
    print("  No browser dialogs, runs completely in background")
    print("=" * 60)
    print()
    
    # Get user ID
    while True:
        try:
            USER_ID = int(input("Enter your User ID: "))
            break
        except ValueError:
            print("Please enter a valid number")
    
    print(f"\n✓ User ID set to: {USER_ID}")
    print("\nChecking if you're clocked in...")
    
    # Get attendance ID
    ATTENDANCE_ID = get_active_attendance()
    
    if ATTENDANCE_ID:
        print(f"✓ Found active attendance (ID: {ATTENDANCE_ID})")
    else:
        print("⚠ No active attendance found. You may not be clocked in.")
        print("  Screenshots will be taken but may fail to upload.")
        print()
        manual_id = input("Enter Attendance ID manually (or press Enter to skip): ")
        if manual_id:
            ATTENDANCE_ID = int(manual_id)
    
    print("\n" + "=" * 60)
    print("  MONITORING STARTED")
    print("=" * 60)
    print(f"  Capturing every {INTERVAL_SECONDS} seconds")
    print("  Press Ctrl+C to stop")
    print("=" * 60)
    print()
    
    screenshot_count = 0
    
    try:
        while True:
            # Check for active attendance if we don't have one
            if not ATTENDANCE_ID:
                ATTENDANCE_ID = get_active_attendance()
                if ATTENDANCE_ID:
                    print(f"\n✓ Attendance detected (ID: {ATTENDANCE_ID})")
            
            if ATTENDANCE_ID:
                screenshot_count += 1
                timestamp = datetime.now().strftime('%H:%M:%S')
                print(f"\n[{timestamp}] 📸 Capturing screenshot #{screenshot_count}...")
                
                image_data = capture_screenshot()
                
                if image_data:
                    size_kb = len(image_data) / 1024
                    print(f"  Size: {size_kb:.2f} KB")
                    upload_screenshot(image_data)
                else:
                    print("  ✗ Capture failed")
            else:
                print(f"[{datetime.now().strftime('%H:%M:%S')}] Waiting for clock-in...")
            
            # Wait for next capture
            time.sleep(INTERVAL_SECONDS)
            
    except KeyboardInterrupt:
        print("\n\n" + "=" * 60)
        print("  MONITORING STOPPED")
        print("=" * 60)
        print(f"  Total screenshots captured: {screenshot_count}")
        print("\nThank you for using CRM Screenshot Monitor!")
        sys.exit(0)

if __name__ == "__main__":
    main()
