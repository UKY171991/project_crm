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
SCREENSHOT_DIR = "screenshots_temp"

# Create temp directory
if not os.path.exists(SCREENSHOT_DIR):
    os.makedirs(SCREENSHOT_DIR)

def capture_screenshot():
    """Capture the entire screen"""
    try:
        # Capture entire screen
        screenshot = ImageGrab.grab(all_screens=True)
        
        # Save temporarily
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        temp_path = os.path.join(SCREENSHOT_DIR, f"screen_{timestamp}.png")
        screenshot.save(temp_path, "PNG")
        
        return temp_path
    except Exception as e:
        print(f"Error capturing screenshot: {e}")
        return None

def upload_screenshot(file_path, user_id, attendance_id):
    """Upload screenshot to server"""
    try:
        with open(file_path, "rb") as f:
            image_data = base64.b64encode(f.read()).decode('utf-8')
        
        payload = {
            "user_id": user_id,
            "attendance_id": attendance_id,
            "image_data": f"data:image/png;base64,{image_data}"
        }
        
        response = requests.post(API_URL, json=payload)
        
        if response.status_code == 200:
            print(f"Screenshot uploaded successfully at {datetime.now()}")
            # Delete temp file
            os.remove(file_path)
            return True
        else:
            print(f"Upload failed: {response.status_code}")
            return False
            
    except Exception as e:
        print(f"Error uploading screenshot: {e}")
        return False

def main():
    print("=== CRM Screenshot Monitor ===")
    print(f"Capturing screen every {INTERVAL_SECONDS} seconds")
    print("Press Ctrl+C to stop\n")
    
    # Get user credentials
    user_id = input("Enter your User ID: ")
    
    try:
        while True:
            # Check if user is clocked in (you can add API call here)
            print(f"\n[{datetime.now().strftime('%H:%M:%S')}] Capturing screenshot...")
            
            screenshot_path = capture_screenshot()
            
            if screenshot_path:
                # For now, we'll assume attendance_id = 1
                # In production, fetch this from the API
                attendance_id = 1
                upload_screenshot(screenshot_path, user_id, attendance_id)
            
            # Wait for next capture
            time.sleep(INTERVAL_SECONDS)
            
    except KeyboardInterrupt:
        print("\n\nScreenshot monitor stopped.")
        sys.exit(0)

if __name__ == "__main__":
    main()
