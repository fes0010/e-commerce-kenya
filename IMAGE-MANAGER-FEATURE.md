# Enhanced Static Content Editor with Image Manager

## What Changed

✅ **Reverted to original Bagisto editor** (HTML, CSS, Preview tabs)
✅ **Added Image Manager** with upload, replace, delete, and insert functionality
✅ **No more visual grid** - you use HTML/CSS as before, but with better image management

## New Features

### 1. **Manage Images Button**
- Shows count of uploaded images: "Manage Images (5)"
- Click to open image manager modal

### 2. **Image Manager Modal**
Opens a gallery showing all uploaded images with:
- **Thumbnail preview** (grid layout)
- **Copy URL** - Copy image URL to clipboard
- **Insert** - Insert image tag into HTML editor at cursor position
- **Replace** - Upload a new image to replace this one (updates HTML automatically)
- **Delete** - Remove image from HTML (with confirmation)

### 3. **Workflow**

#### Upload Images:
1. Click **"Add Image"** button
2. Select image file (auto-optimized to WebP)
3. Image uploads and inserts into HTML at cursor position

#### Manage Uploaded Images:
1. Click **"Manage Images (X)"** button
2. See all uploaded images in grid
3. Hover over any image to see action buttons:
   - **Copy URL**: Copy to clipboard for manual use
   - **Insert**: Add `<img>` tag to HTML at cursor
   - **Replace**: Upload new file, old URL replaced everywhere in HTML
   - **Delete**: Remove all occurrences from HTML

## How to Use

### Basic Workflow:
1. Go to: `https://ecommerce.munene.shop/admin/settings/themes/edit/3`
2. You'll see the original **HTML**, **CSS**, **Preview** tabs
3. Click **"Add Image"** to upload images
4. Images auto-insert into HTML editor
5. Click **"Manage Images"** to see all uploaded images
6. Use **Insert** to add images to HTML without typing
7. Use **Replace** to swap images (HTML updates automatically)
8. Use **Delete** to remove images from HTML

### Example: Add Banner Image
1. Click **"Add Image"** → Upload banner.jpg
2. Image inserts: `<img class="lazy" src="" data-src="storage/theme/3/xxx.webp">`
3. Edit HTML to add classes: `<img class="lazy banner-img" src="" data-src="storage/theme/3/xxx.webp">`
4. Switch to **CSS** tab
5. Add styles:
   ```css
   .banner-img {
       width: 100%;
       height: 400px;
       object-fit: cover;
   }
   ```
6. Switch to **Preview** to see result

### Example: Replace Image
1. Click **"Manage Images"**
2. Find the image you want to replace
3. Click **"Replace"**
4. Upload new image
5. ✅ All occurrences in HTML automatically updated!

### Example: Delete Image
1. Click **"Manage Images"**
2. Find the image to delete
3. Click **"Delete"**
4. Confirm
5. ✅ All `<img>` tags with that URL removed from HTML!

## Benefits

✅ **Original HTML/CSS/Preview workflow** - No learning curve
✅ **Easy image upload** - Click button, select file, done
✅ **Visual image gallery** - See all uploaded images
✅ **Quick insert** - No typing URLs manually
✅ **Safe replace** - Update image everywhere with one click
✅ **Clean delete** - Remove image from HTML automatically
✅ **Auto-optimization** - Images converted to WebP, resized

## Technical Details

### Image Upload:
- Accepts: JPEG, PNG, WebP, GIF (up to 10MB)
- Auto-converts to WebP format
- Auto-resizes to max 1920x1080
- Optimizes to 85% quality
- Returns URL: `storage/theme/{id}/{random}.webp`

### Image Manager:
- Extracts images from HTML using regex
- Tracks all `src` and `data-src` attributes
- Shows unique images only (no duplicates)
- Updates HTML when replacing/deleting

### Replace Functionality:
- Uploads new image
- Finds all occurrences of old URL in HTML
- Replaces with new URL
- Updates CodeMirror editor
- Updates image gallery

### Delete Functionality:
- Finds all `<img>` tags with that URL
- Removes them from HTML
- Updates CodeMirror editor
- Removes from gallery

## Git Status

✅ **Committed**: `cc295b55e3`
✅ **Pushed to GitHub**: `https://github.com/fes0010/e-commerce-kenya.git`

## Deployment

### Option 1: Redeploy in Dokploy
1. Go to Dokploy dashboard
2. Click **"Redeploy"** (with "No Cache")
3. Wait for deployment
4. Test at: `https://ecommerce.munene.shop/admin/settings/themes/edit/3`

### Option 2: Hot-Patch (Immediate)
```bash
# Find container
docker ps | grep ecommerce

# Copy files
docker cp packages/Webkul/Admin/src/Resources/views/settings/themes/edit.blade.php apps-ecommerce-4zagpn.1.XXXXX:/var/www/html/packages/Webkul/Admin/src/Resources/views/settings/themes/edit.blade.php

docker cp packages/Webkul/Admin/src/Resources/views/settings/themes/edit/static-content.blade.php apps-ecommerce-4zagpn.1.XXXXX:/var/www/html/packages/Webkul/Admin/src/Resources/views/settings/themes/edit/static-content.blade.php

# Clear caches
docker exec apps-ecommerce-4zagpn.1.XXXXX php artisan view:clear
docker exec apps-ecommerce-4zagpn.1.XXXXX php artisan cache:clear
```

## Verification

After deployment, test:

1. ✅ HTML/CSS/Preview tabs work
2. ✅ "Add Image" button uploads and inserts
3. ✅ "Manage Images" button shows gallery
4. ✅ Image count displays correctly
5. ✅ "Copy URL" copies to clipboard
6. ✅ "Insert" adds image to HTML
7. ✅ "Replace" uploads and updates HTML
8. ✅ "Delete" removes from HTML
9. ✅ Images display in Preview tab
10. ✅ Save button stores changes

## Comparison

### Before (Original Bagisto):
- ✅ HTML/CSS/Preview tabs
- ✅ Add Image button
- ❌ No way to see uploaded images
- ❌ No way to replace images
- ❌ No way to delete images
- ❌ Manual URL typing required

### After (Enhanced):
- ✅ HTML/CSS/Preview tabs (same)
- ✅ Add Image button (same)
- ✅ **Image Manager with gallery**
- ✅ **Copy URL to clipboard**
- ✅ **Insert images easily**
- ✅ **Replace images (updates HTML)**
- ✅ **Delete images (removes from HTML)**
- ✅ **No manual URL typing needed**

## Files Modified

1. `packages/Webkul/Admin/src/Resources/views/settings/themes/edit.blade.php` (reverted to original)
2. `packages/Webkul/Admin/src/Resources/views/settings/themes/edit/static-content.blade.php` (enhanced with image manager)

---

**Status**: ✅ Ready for deployment
**Commit**: `cc295b55e3`
**Next**: Redeploy in Dokploy or hot-patch for immediate testing
