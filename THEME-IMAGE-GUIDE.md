# Theme Image Management Guide - E-Commerce Kenya

## 🎨 Easy Image Upload for Themes (No HTML Required!)

### New Visual Editor Features

We've created an improved theme customization interface that allows you to add images without writing any HTML code.

---

## 📁 Files Created

1. **images-improved.blade.php** - Enhanced image upload component with preview, change, and delete
2. **static-content-improved.blade.php** - Visual theme editor (no HTML knowledge needed)

---

## 🚀 How to Use the Visual Theme Editor

### Step 1: Access Theme Settings

1. Go to **Admin Panel** → **Settings** → **Themes**
2. Click **Edit** on any theme
3. You'll see two modes:
   - **Visual Editor** (Easy mode - No coding!)
   - **Code Editor** (For advanced users)

### Step 2: Add Images Visually

#### Using the Visual Editor:

1. Click **"Visual Editor"** button
2. In the **Image Gallery** section:
   - Click **"Add Image"** button
   - Or click the **"+"** card to upload
3. Select your image file
4. Add image description (alt text)
5. Optionally add a link URL
6. Done! No HTML needed!

#### Image Actions:

- **Preview**: Hover over image → Click eye icon
- **Change**: Hover over image → Click edit icon → Select new image
- **Delete**: Hover over image → Click delete icon → Confirm

### Step 3: Add Text Content

1. Scroll to **Text Content** section
2. Type or paste your text
3. No HTML tags needed!

### Step 4: Choose Layout

Select from 4 layout options:
- **Grid**: Images in a responsive grid
- **Slider**: Carousel/slideshow
- **Masonry**: Pinterest-style layout
- **Banner**: Full-width banner with text overlay

### Step 5: Preview & Save

1. Check the **Preview** section to see how it looks
2. Click **Save** button
3. View on your website!

---

## 🖼️ Image Upload Locations

### 1. Channel Logo & Favicon

**Location**: Settings → Channels → Edit Channel

**Features**:
- ✅ Drag & drop upload
- ✅ Image preview
- ✅ Change/delete options
- ✅ Recommended sizes shown

**How to Upload**:
```
1. Go to Settings → Channels
2. Click Edit on your channel
3. Scroll to "Logo and Design" section
4. Click on logo/favicon area
5. Select image
6. Save
```

### 2. Theme Image Carousel

**Location**: Settings → Themes → Image Carousel

**Features**:
- ✅ Multiple images
- ✅ Drag to reorder
- ✅ Add title and link per image
- ✅ Preview before save

**How to Upload**:
```
1. Go to Settings → Themes
2. Find "Image Carousel" theme
3. Click Edit
4. Click "Add Slider" button
5. Fill in title
6. Upload image
7. Add link (optional)
8. Save
```

### 3. Static Content (NEW - Visual Editor)

**Location**: Settings → Themes → Static Content

**Features**:
- ✅ No HTML knowledge required
- ✅ Visual image manager
- ✅ Multiple layout options
- ✅ Live preview
- ✅ Text content editor

**How to Upload**:
```
1. Go to Settings → Themes
2. Find "Static Content" theme
3. Click Edit
4. Click "Visual Editor" button
5. Click "Add Image" or "+" card
6. Upload images
7. Add descriptions
8. Choose layout
9. Preview
10. Save
```

### 4. Product Images

**Location**: Catalog → Products → Edit Product

**Features**:
- ✅ Multiple images per product
- ✅ Drag to reorder
- ✅ Set featured image
- ✅ Automatic thumbnails

### 5. Category Images

**Location**: Catalog → Categories → Edit Category

**Features**:
- ✅ Category banner
- ✅ Category icon
- ✅ Preview

---

## 🔧 Implementation Steps

### Option 1: Replace Existing Component (Recommended)

Replace the default image component with the improved version:

```bash
# Backup original
cp packages/Webkul/Admin/src/Resources/views/components/media/images.blade.php packages/Webkul/Admin/src/Resources/views/components/media/images.blade.php.backup

# Replace with improved version
cp packages/Webkul/Admin/src/Resources/views/components/media/images-improved.blade.php packages/Webkul/Admin/src/Resources/views/components/media/images.blade.php
```

### Option 2: Use Alongside Existing

Keep both versions and use the improved one where needed:

In your blade files, use:
```php
<x-admin::media.images-improved
    name="logo"
    :uploaded-images="$channel->logo ? [['id' => 'logo', 'url' => $channel->logo_url]] : []"
/>
```

### Option 3: Replace Static Content Theme

```bash
# Backup original
cp packages/Webkul/Admin/src/Resources/views/settings/themes/edit/static-content.blade.php packages/Webkul/Admin/src/Resources/views/settings/themes/edit/static-content.blade.php.backup

# Replace with improved version
cp packages/Webkul/Admin/src/Resources/views/settings/themes/edit/static-content-improved.blade.php packages/Webkul/Admin/src/Resources/views/settings/themes/edit/static-content.blade.php
```

---

## ✨ Features Comparison

### Before (Original)

- ❌ No image preview
- ❌ Hard to change images
- ❌ No delete confirmation
- ❌ Small upload area
- ❌ Requires HTML knowledge for themes

### After (Improved)

- ✅ Large image preview
- ✅ Easy change with edit button
- ✅ Delete with confirmation
- ✅ Larger, clearer upload area
- ✅ Visual theme editor (no HTML!)
- ✅ Multiple layout options
- ✅ Live preview
- ✅ Mobile-friendly interface

---

## 🎯 Supported Image Formats

- **JPEG** (.jpg, .jpeg)
- **PNG** (.png)
- **GIF** (.gif)
- **WEBP** (.webp)
- **SVG** (.svg) - for logos/icons

---

## 📏 Recommended Image Sizes

| Location | Recommended Size | Format |
|----------|-----------------|--------|
| Logo | 200x60px | PNG/SVG |
| Favicon | 32x32px | PNG/ICO |
| Slider Images | 1920x600px | JPEG/WEBP |
| Product Images | 800x800px | JPEG/WEBP |
| Category Banners | 1200x400px | JPEG/WEBP |
| Thumbnails | 300x300px | JPEG/WEBP |

---

## 🐛 Troubleshooting

### Images Not Showing?

1. **Check storage link**:
```bash
php artisan storage:link
```

2. **Check permissions**:
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

3. **Clear cache**:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Upload Fails?

1. **Check file size limit** in `php.ini`:
```ini
upload_max_filesize = 50M
post_max_size = 50M
```

2. **Check storage space**:
```bash
df -h
```

3. **Check file permissions**:
```bash
ls -la storage/app/public/
```

---

## 🚀 Quick Start Checklist

- [ ] Run `php artisan storage:link`
- [ ] Set permissions: `chmod -R 775 storage`
- [ ] Upload logo in Settings → Channels
- [ ] Upload favicon in Settings → Channels
- [ ] Add slider images in Settings → Themes → Image Carousel
- [ ] Use Visual Editor for static content (no HTML!)
- [ ] Clear browser cache (Ctrl+Shift+R)
- [ ] Test on mobile devices

---

## 📝 Tips for Best Results

1. **Optimize images before upload**:
   - Use tools like TinyPNG or ImageOptim
   - Target file size: < 200KB for web

2. **Use descriptive alt text**:
   - Helps with SEO
   - Improves accessibility

3. **Consistent image sizes**:
   - Makes your site look professional
   - Faster page loading

4. **Use WEBP format**:
   - Smaller file sizes
   - Better quality
   - Automatic conversion in Bagisto

5. **Test on mobile**:
   - Images should be responsive
   - Check loading speed

---

## 🎓 Video Tutorials (Coming Soon)

- [ ] How to upload logo and favicon
- [ ] Creating image sliders
- [ ] Using the visual theme editor
- [ ] Optimizing images for web
- [ ] Mobile-responsive images

---

## 📞 Need Help?

- **Documentation**: See SETUP-GUIDE.md
- **Issues**: https://github.com/fes0010/e-commerce-kenya/issues
- **Bagisto Docs**: https://devdocs.bagisto.com

---

**Last Updated**: May 30, 2026  
**Version**: 2.0.0  
**Status**: Ready to Use ✅
