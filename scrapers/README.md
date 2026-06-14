# Nila Baby Shop Scraper

Web scraper for https://nilababyshop.com/ built with Scrapling.

## Features

- Scrapes all products from the homepage
- Extracts complete product information:
  - Product name
  - Price (when available)
  - Categories
  - Descriptions and features
  - All product images
- Outputs clean JSON format
- Progress indicators during scraping

## Requirements

- Python 3.8+
- Scrapling library

## Installation

```bash
pip install scrapling
```

## Usage

```bash
python nila_baby_shop_scraper.py
```

## Output

The scraper generates `nila_products.json` containing an array of product objects:

```json
[
  {
    "url": "https://nilababyshop.com/product/...",
    "name": "Product Name",
    "price": "KShs 1,500.00",
    "brand": null,
    "categories": ["Category", "Subcategory"],
    "description": "Product description...",
    "features": ["Feature 1", "Feature 2"],
    "images": ["image_url_1", "image_url_2", ...]
  }
]
```

## Notes

- Many products have variable pricing (size/color options), so price may be null
- Brand information is not available on this website
- Descriptions are cleaned and truncated to 500 characters
- All product images are captured in high resolution

## Statistics

Latest run (June 13, 2026):
- Total products scraped: 163
- Categories: 7 main categories
- Average images per product: 5-8
