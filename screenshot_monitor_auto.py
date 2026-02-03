import time
import requests
import base64
from PIL import ImageGrab
from datetime import datetime
import os
import sys
import json

# Configuration
CRM_URL = "https://crm.devloper.space"
API_URL = f"{CRM_URL}/api/screenshot-upload"
ATTENDANCE_API = f"{CRM_URL}/api/get-active-attendance"
LOGIN_API = f"{CRM_URL}/api/login"
USER_INFO_API = f"{CRM_URL}/api/user-info"
INTERVAL_SECONDS = 60  # 1 minute

# Global variables
USER_ID = None
ATTENDANCE_ID = None
AUTH_TOKEN = None
session = requests.Session()

def login_to_crm(email, password):
    """Login to CRM and get user info"""
    global USER_ID, AUTH_TOKEN
    
    try:
        print("\n🔐 Logging in to CRM...")
        
        # Try to get CSRF token first
        response = session.get(CRM_URL)
        
        # Attempt login
        login_data = {
            'email': email,
            'password': password
        }
        
        response = session.post(f"{CRM_URL}/login", data=login_data, allow_redirects=True)
        
        if response.status_code == 200 and 'dashboard' in response.url:
            print("✓ Login successful!")
            
            # Try to get user info from the session
            user_response = session.get(f"{CRM_URL}/api/user-info")
            
            if user_response.status_code == 200:
                user_data = user_response.json()
                USER_ID = user_data.get('id')
                print(f"✓ User ID detected: {USER_ID}")
                return True
            else:
                # Fallback: parse from page
                print("⚠ Could not get user info from API, trying alternative method...")
                return try_get_user_from_page()
        else:
            print("✗ Login failed. Please check your credentials.")
            return False
            
    except Exception as e:
        print(f"✗ Login error: {e}")
        return False

def try_get_user_from_page():
    """Try to extract user ID from dashboard page"""
    global USER_ID
    
    try:
        response = session.get(f"{CRM_URL}/dashboard")
        
        # Look for user ID in meta tags or JavaScript
        if 'user' in response.text.lower():
            # Try to find user ID in the page
            import re
            
            # Look for common patterns
            patterns = [
                r'"user_id"\s*:\s*(\d+)',
                r'"id"\s*:\s*(\d+)',
                r'user_id=(\d+)',
                r'userId:\s*(\d+)'
            ]
            
            for pattern in patterns:
                match = re.search(pattern, response.text)
                if match:
                    USER_ID = int(match.group(1))
                    print(f"✓ User ID detected: {USER_ID}")
                    return True
        
        return False
    except:
        return False

def get_active_attendance():
    """Get active attendance ID from server"""
    global ATTENDANCE_ID
    
    if not USER_ID:
        return None
    
    try:
        # Try with session cookies
        response = session.get(f"{CRM_URL}/api/get-active-attendance?user_id={USER_ID}")
        
        if response.status_code == 200:
            data = response.json()
            if data.get('clocked_in'):
                ATTENDANCE_ID = data.get('attendance_id')
                return ATTENDANCE_ID
        
        # Fallback to POST
        response = session.post(
            ATTENDANCE_API,
            json={'user_id': USER_ID}
        )
        
        if response.status_code == 200:
            data = response.json()
            if data.get('clocked_in'):
                ATTENDANCE_ID = data.get('attendance_id')
                return ATTENDANCE_ID
                
        return None
    except Exception as e:
        print(f"⚠ Could not get attendance: {e}")
        return None

def capture_screenshot():
    """Capture the entire screen - NO DIALOGS, NO PROMPTS"""
    try:
        screenshot = ImageGrab.grab(all_screens=True)
        
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
        
        response = session.post(API_URL, json=payload, timeout=10)
        
        if response.status_code == 200:
            result = response.json()
            print(f"✓ Screenshot uploaded (ID: {result.get('screenshot_id', 'N/A')})")
            return True
        else:
            print(f"✗ Upload failed: {response.status_code}")
            return False
            
    except Exception as e:
        print(f"✗ Upload error: {e}")
        return False

def main():
    global USER_ID, ATTENDANCE_ID
    
    print("=" * 70)
    print("  CRM SCREENSHOT MONITOR - AUTO-LOGIN VERSION")
    print("  Automatically detects User ID and Attendance ID")
    print("=" * 70)
    print()
    
    # Get login credentials
    print("Please enter your CRM login credentials:")
    email = input("Email: ")
    password = input("Password: ")
    
    # Login to CRM
    if not login_to_crm(email, password):
        print("\n✗ Could not login. Please check your credentials and try again.")
        input("\nPress Enter to exit...")
        sys.exit(1)
    
    # If we still don't have user ID, ask manually
    if not USER_ID:
        print("\n⚠ Could not auto-detect User ID")
        while True:
            try:
                USER_ID = int(input("Please enter your User ID manually: "))
                break
            except ValueError:
                print("Please enter a valid number")
    
    print(f"\n✓ User ID: {USER_ID}")
    print("\nChecking if you're clocked in...")
    
    # Get attendance ID
    ATTENDANCE_ID = get_active_attendance()
    
    if ATTENDANCE_ID:
        print(f"✓ Active attendance found (ID: {ATTENDANCE_ID})")
    else:
        print("⚠ No active attendance. Please clock in first.")
        print("\nWaiting for you to clock in...")
        
        # Wait for clock-in
        while not ATTENDANCE_ID:
            time.sleep(10)
            ATTENDANCE_ID = get_active_attendance()
            if ATTENDANCE_ID:
                print(f"\n✓ Clocked in! Attendance ID: {ATTENDANCE_ID}")
                break
            else:
                print(".", end="", flush=True)
    
    print("\n" + "=" * 70)
    print("  MONITORING STARTED")
    print("=" * 70)
    print(f"  Capturing every {INTERVAL_SECONDS} seconds")
    print("  Press Ctrl+C to stop")
    print("=" * 70)
    print()
    
    screenshot_count = 0
    
    try:
        while True:
            # Refresh attendance ID periodically
            if screenshot_count % 5 == 0:  # Every 5 screenshots
                temp_id = get_active_attendance()
                if temp_id and temp_id != ATTENDANCE_ID:
                    ATTENDANCE_ID = temp_id
                    print(f"\n✓ Attendance updated (ID: {ATTENDANCE_ID})")
            
            if ATTENDANCE_ID:
                screenshot_count += 1
                timestamp = datetime.now().strftime('%H:%M:%S')
                print(f"\n[{timestamp}] 📸 Screenshot #{screenshot_count}...", end=" ")
                
                image_data = capture_screenshot()
                
                if image_data:
                    size_kb = len(image_data) / 1024
                    print(f"({size_kb:.1f} KB)", end=" ")
                    upload_screenshot(image_data)
                else:
                    print("✗ Capture failed")
            else:
                print(f"[{datetime.now().strftime('%H:%M:%S')}] Waiting for clock-in...")
                ATTENDANCE_ID = get_active_attendance()
            
            time.sleep(INTERVAL_SECONDS)
            
    except KeyboardInterrupt:
        print("\n\n" + "=" * 70)
        print("  MONITORING STOPPED")
        print("=" * 70)
        print(f"  Total screenshots: {screenshot_count}")
        print("\nThank you!")
        sys.exit(0)

if __name__ == "__main__":
    main()
