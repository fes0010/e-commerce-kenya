#!/usr/bin/env python3
"""Scraper for nilababyshop.com - extracts product data"""

from scrapling import Fetcher
import json
import re

def scrape_product(url, fetcher):
    """Scrape a single product page"""
    try:
        page = fetcher.get(url)
        
        # Extract product name
        name = page.css('h1::text').get() or ''
        name = name.strip()
        
        # Extract price
        price_elem = page.css('.price').get() or ''
        price = re.search(r'KShs\s*([\d,]+\.?\d*)', price_elem)
        price = price.group(0) if price else None
        
        # Extract categories from breadcrumb
        categories = [c.strip() for c in page.css('.woocommerce-breadcrumb a::text').getall() if c.strip() not in ['Home']]
        
        # Extract description text
        desc_section = page.css('#tab-description').get() or ''
        # Clean HTML and get text
        description = re.sub(r'<[^>]+>', ' ', desc_section)
        description = re.sub(r'\s+', ' ', description).strip()
        
        # Extract short features from bullet points
        features = [f.strip() for f in page.css('#tab-description li::text').getall() if f.strip()]
        
        # Extract all images
        images = page.css('.woocommerce-product-gallery__image img::attr(src)').getall()
        if not images:
            images = page.css('img[alt*="{}"]::attr(src)'.format(name.split()[0] if name else 'product')).getall()
        
        product = {
            'url': url,
            'name': name,
            'price': price,
            'brand': None,  # Not available on this site
            'categories': categories,
            'description': description[:500] if description else None,
            'features': features,
            'images': images
        }
        
        return product
    except Exception as e:
        print(f"Error scraping {url}: {e}")
        return None

def scrape_shop():
    """Scrape all products from all categories with pagination"""
    fetcher = Fetcher()
    base_url = 'https://nilababyshop.com'
    
    print(f"Discovering all product URLs...")
    
    product_links = set()
    
    # Get product links from main shop page with pagination
    page_num = 1
    while page_num <= 50:  # Safety limit
        url = f'{base_url}/shop/page/{page_num}/' if page_num > 1 else f'{base_url}/shop/'
        print(f"  Scanning shop page {page_num}...")
        
        try:
            page = fetcher.get(url)
            # Only get actual product links (not social shares)
            links = [link for link in page.css('a[href*="/product/"]::attr(href)').getall() 
                    if link.startswith(base_url + '/product/') 
                    and not any(x in link for x in ['facebook', 'twitter', 'whatsapp', 'threads', 'add-to-cart', 'add-to-wishlist'])]
            
            if not links:
                break
                
            before = len(product_links)
            product_links.update(links)
            new_count = len(product_links) - before
            
            if new_count == 0:
                break
                
            print(f"    Found {new_count} new products (total: {len(product_links)})")
            page_num += 1
        except:
            break
    
    print(f"\n{'='*60}")
    print(f"Found {len(product_links)} unique products")
    print(f"{'='*60}\n")
    
    products = []
    for i, link in enumerate(sorted(product_links), 1):
        slug = link.split('/')[-2] if link.endswith('/') else link.split('/')[-1]
        print(f"[{i}/{len(product_links)}] {slug[:50]}...")
        product = scrape_product(link, fetcher)
        if product and product['name']:
            products.append(product)
            print(f"  ✓ {product['name'][:60]}")
        else:
            print(f"  ✗ Failed")
    
    return products

if __name__ == '__main__':
    print("=" * 60)
    print("Nila Baby Shop Scraper")
    print("=" * 60)
    
    products = scrape_shop()
    
    # Save to JSON
    output_file = 'nila_products.json'
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(products, indent=2, ensure_ascii=False, fp=f)
    
    print("\n" + "=" * 60)
    print(f"✓ Scraped {len(products)} products")
    print(f"✓ Saved to {output_file}")
    print("=" * 60)
