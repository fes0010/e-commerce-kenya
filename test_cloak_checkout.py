import sys
import time
from cloakbrowser import launch

def run():
    print("Launching CloakBrowser...")
    browser = launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    console_logs = []
    # Log console messages
    page.on("console", lambda msg: console_logs.append(f"[{msg.type}] {msg.text}"))
    # Log unhandled exceptions
    page.on("pageerror", lambda err: console_logs.append(f"[EXCEPTION] {err}"))
    # Log network requests/responses
    def handle_response(response):
        if response.status >= 400:
            console_logs.append(f"[NETWORK ERROR] {response.status} {response.url}")
            try:
                text = response.text()
                console_logs.append(f"[RESPONSE BODY] {text[:500]}")
            except Exception:
                pass
    page.on("response", handle_response)

    try:
        # Step 1: Open homepage
        print("Navigating to home page...")
        page.goto("https://ecommerce.munene.shop/")
        page.wait_for_load_state("networkidle")
        page.screenshot(path="step1_home.png")

        # Step 2: Search product
        print("Searching for product...")
        search_input = page.locator('input[placeholder="Search products here"]')
        search_input.fill("11 Pieces Fleece Unisex Newborn Baby Receiving Set")
        search_input.press("Enter")
        page.wait_for_load_state("networkidle")
        page.screenshot(path="step2_search.png")

        # Step 3: Add to cart
        print("Adding product to cart...")
        add_to_cart = page.get_by_role("button", name="Add To Cart").first
        add_to_cart.click()
        page.wait_for_timeout(3000)
        page.screenshot(path="step3_cart_added.png")

        # Step 4: Proceed to checkout
        print("Navigating to cart page...")
        page.goto("https://ecommerce.munene.shop/checkout/cart")
        page.wait_for_load_state("networkidle")
        page.screenshot(path="step4_cart.png")

        # Select all items if needed (click checkbox next to "0 Items Selected")
        print("Selecting items in cart...")
        checkbox = page.locator('input[type="checkbox"]').first
        if checkbox.is_visible():
            checkbox.click()
            page.wait_for_timeout(1000)
            page.screenshot(path="step4_cart_selected.png")

        print("Clicking Proceed To Checkout...")
        # Try both text variations just in case
        proceed_btn = page.get_by_text("Proceed To Checkout").first
        if not proceed_btn.is_visible():
            proceed_btn = page.get_by_text("Continue to Checkout").first
        proceed_btn.click()
        page.wait_for_load_state("networkidle")
        page.screenshot(path="step5_checkout_address.png")

        # Step 5: Fill address (guest form)
        print("Filling guest address...")
        page.get_by_role("textbox", name="Company Name").fill("Web")
        page.get_by_role("textbox", name="First Name").fill("demo")
        page.get_by_role("textbox", name="Last Name").fill("guest")
        page.get_by_role("textbox", name="email@example.com").fill("demo@example.com")
        page.get_by_role("textbox", name="Street Address").fill("north street")
        page.locator('select[name="billing\\.country"]').select_option("KE")
        page.wait_for_timeout(1000)
        page.locator('select[name="billing\\.state"]').select_option("KE-30")
        page.get_by_role("textbox", name="City").fill("Nairobi")
        page.get_by_role("textbox", name="Zip/Postcode").fill("00100")
        page.get_by_role("textbox", name="Telephone").fill("+254712345678")
        page.screenshot(path="step6_address_filled.png")

        # Click Proceed
        print("Clicking Proceed/Save button...")
        proceed_btn = page.get_by_role("button", name="Proceed").first
        if not proceed_btn.is_visible():
            proceed_btn = page.get_by_role("button", name="Save").first
        proceed_btn.click()
        page.wait_for_timeout(2000)
        page.screenshot(path="step7_address_saved.png")

        # Step 6: Shipping Method
        print("Selecting shipping method...")
        flat_rate = page.get_by_text("Flat Rate").first
        if flat_rate.is_visible():
            flat_rate.click()
        else:
            page.get_by_text("Free Shipping").first.click()
        page.wait_for_timeout(1000)
        page.screenshot(path="step8_shipping_selected.png")

        # Step 7: Payment Method
        print("Selecting payment method...")
        mpesa = page.get_by_text("mpesa").first
        if mpesa.is_visible():
            mpesa.click()
            print("M-Pesa selected")
        else:
            cod = page.get_by_text("Cash On Delivery").first
            if cod.is_visible():
                cod.click()
                print("Cash on Delivery selected")
            else:
                transfer = page.get_by_text("Money Transfer").first
                if transfer.is_visible():
                    transfer.click()
                    print("Money Transfer selected")
        page.wait_for_timeout(1000)
        page.screenshot(path="step9_payment_selected.png")

        # Step 8: Place Order
        print("Clicking Place Order...")
        place_order_btn = page.get_by_role("button", name="Place Order").first
        place_order_btn.click()
        print("Waiting for order completion...")
        page.wait_for_timeout(10000)
        page.screenshot(path="step10_order_placed.png")

        print("Final URL:", page.url)

    except Exception as e:
        print(f"Exception during run: {e}")
        page.screenshot(path="error.png")

    finally:
        browser.close()
        print("\n--- Console Logs & Network Errors ---")
        for log in console_logs:
            print(log)
        print("--------------------")

if __name__ == '__main__':
    run()
