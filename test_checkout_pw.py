"""
Playwright checkout test for https://ecommerce.munene.shop/
Captures screenshots at each step and logs all console errors / network 4xx/5xx
"""
import json
import time
from playwright.sync_api import sync_playwright

SCREENSHOTS = []


def save_shot(page, name):
    path = f"/home/freeman/commerce/{name}.png"
    page.screenshot(path=path, full_page=True)
    SCREENSHOTS.append((name, path))
    print(f"  📸 {name}.png saved")


def run():
    console_logs = []
    network_errors = []

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True, args=["--no-sandbox"])
        context = browser.new_context(
            viewport={"width": 1440, "height": 900},
            user_agent="Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
        )
        page = context.new_page()

        # --- Listeners ---
        page.on("console", lambda msg: console_logs.append(f"[{msg.type.upper()}] {msg.text}"))
        page.on("pageerror", lambda err: console_logs.append(f"[PAGE-ERROR] {err}"))

        def on_response(resp):
            if resp.status >= 400:
                body = ""
                try:
                    body = resp.text()[:800]
                except Exception:
                    pass
                network_errors.append({
                    "status": resp.status,
                    "url": resp.url,
                    "body": body,
                })

        page.on("response", on_response)

        try:
            # ── Step 1: Homepage ──────────────────────────────────────────────
            print("\n[1] Loading homepage...")
            page.goto("https://nilababyshop.store/", wait_until="networkidle", timeout=30000)
            save_shot(page, "s01_home")

            # ── Step 2: Search for a product ─────────────────────────────────
            print("[2] Searching for a product...")
            search = page.locator('input[placeholder*="Search"]').first
            search.fill("baby set")
            search.press("Enter")
            page.wait_for_load_state("networkidle", timeout=20000)
            save_shot(page, "s02_search_results")

            # ── Step 3: Add first product to cart ────────────────────────────
            print("[3] Adding first product to cart...")
            # Try clicking first product link to go to product page
            first_product = page.locator("a.product-name, .product-card a, article a").first
            if first_product.count() > 0:
                first_product.click()
                page.wait_for_load_state("networkidle", timeout=15000)
                save_shot(page, "s03_product_page")

                add_btn = page.get_by_role("button", name="Add To Cart").first
                add_btn.click()
                page.wait_for_timeout(3000)
                save_shot(page, "s04_cart_added")
            else:
                # Fallback: direct Add to Cart button on listing
                add_btn = page.get_by_role("button", name="Add To Cart").first
                add_btn.click()
                page.wait_for_timeout(3000)
                save_shot(page, "s04_cart_added")

            # ── Step 4: Cart page ─────────────────────────────────────────────
            print("[4] Going to cart...")
            page.goto("https://nilababyshop.store/checkout/cart", wait_until="networkidle", timeout=20000)
            save_shot(page, "s05_cart_page")
            print(f"    Cart page HTML snippet:\n{page.content()[:2000]}\n")

            # ── Step 5: Proceed to checkout ───────────────────────────────────
            print("[5] Proceeding to checkout...")
            page.goto("https://nilababyshop.store/checkout/onepage", wait_until="networkidle", timeout=30000)
            save_shot(page, "s06_checkout_onepage")

            # Capture full page text for error analysis
            page_text = page.inner_text("body")
            print(f"\n    Page text (first 3000 chars):\n{page_text[:3000]}\n")

            # Look for error messages
            errors = page.locator("[class*='error'], [class*='alert'], [class*='notice'], .flash-message").all()
            if errors:
                print("    Visible error elements:")
                for el in errors:
                    print(f"      → {el.inner_text()}")

            # Check page title / URL
            print(f"    URL: {page.url}")
            print(f"    Title: {page.title()}")

            # ── Step 6: Fill address form (guest) ────────────────────────────
            print("[6] Attempting to fill address...")
            page.wait_for_timeout(2000)

            # Check what's visible on the checkout page
            inputs = page.locator("input, select, textarea").all()
            print(f"    Found {len(inputs)} input elements")
            for inp in inputs[:10]:
                try:
                    print(f"      input: name={inp.get_attribute('name')} type={inp.get_attribute('type')} placeholder={inp.get_attribute('placeholder')}")
                except Exception:
                    pass

            save_shot(page, "s07_checkout_detail")

        except Exception as e:
            print(f"\n❌ Exception: {e}")
            save_shot(page, "s_error")

        finally:
            # ── Summary ───────────────────────────────────────────────────────
            browser.close()

            print("\n" + "="*60)
            print("CONSOLE LOGS:")
            print("="*60)
            for log in console_logs:
                print(log)

            print("\n" + "="*60)
            print(f"NETWORK ERRORS ({len(network_errors)} total):")
            print("="*60)
            for err in network_errors:
                print(f"\n  [{err['status']}] {err['url']}")
                if err['body']:
                    print(f"  BODY: {err['body'][:500]}")

            print("\n" + "="*60)
            print("SCREENSHOTS SAVED:")
            for name, path in SCREENSHOTS:
                print(f"  {path}")


if __name__ == "__main__":
    run()
