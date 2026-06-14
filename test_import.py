import sys
try:
    from cloakbrowser import launch
    print("Successfully imported cloakbrowser")
    browser = launch(headless=True)
    print("Successfully launched cloakbrowser")
    browser.close()
    print("Closed browser successfully")
except Exception as e:
    print(f"Error: {e}")
    sys.exit(1)
