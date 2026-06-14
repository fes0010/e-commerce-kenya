import json
import re
import urllib.request
import concurrent.futures

with open('scrapers/nila_products.json', 'r', encoding='utf-8') as f:
    products = json.load(f)

def fetch_price(product):
    if product.get('price'):
        return product
        
    url = product['url']
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req, timeout=10) as response:
            html = response.read().decode('utf-8')
            
            # Find the price element. It usually looks like <p class="price">...<span class="woocommerce-Price-amount amount">
            price_match = re.search(r'class="price"[\s\S]*?(?:KShs|Ksh|Kshs)[^0-9]*([0-9,]+\.?[0-9]*)', html, re.IGNORECASE)
            if not price_match:
                # Try anywhere in the body
                price_match = re.search(r'(?:KShs|Ksh|Kshs)[^0-9]*([0-9,]+\.?[0-9]*)', html, re.IGNORECASE)
            
            if price_match:
                product['price'] = price_match.group(1).replace(',', '')
                print(f"Got price for {product['name']}: {product['price']}")
            else:
                print(f"Could not find price for {product['name']}")
    except Exception as e:
        print(f"Error fetching {url}: {e}")
        
    return product

print("Fetching missing prices...")
with concurrent.futures.ThreadPoolExecutor(max_workers=10) as executor:
    products = list(executor.map(fetch_price, products))

with open('scrapers/nila_products.json', 'w', encoding='utf-8') as f:
    json.dump(products, f, indent=2, ensure_ascii=False)

print("Done updating prices!")
