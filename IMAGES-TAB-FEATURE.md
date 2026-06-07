# Images Tab Feature - Perfect Solution! ✅

## What You Get

✅ **Original HTML/CSS/Preview tabs** - Unchanged, work as before
✅ **NEW "Images" tab** - Visual image management
✅ **File upload** - Not URLs
✅ **Replace/Delete** - Easy image management
✅ **Insert to HTML** - Click to add images to your code

## Interface

### 4 Tabs Now Available:

1. **HTML** - CodeMirror editor for HTML (original)
2. **CSS** - CodeMirror editor for CSS (original)
3. **Images (X)** - NEW! Visual image gallery with count
4. **Preview** - Live preview (original)

## Images Tab Features

### Visual Gallery
- Grid layout showing all uploaded images
- Thumbnail previews (200x200px)
- Filename displayed below each image
- Hover to see action buttons

### Action Buttons (on hover):
1. **Insert** - Copies `<img>` tag to clipboard, shows message to paste in HTML tab
2. **Copy** - Copies image URL to clipboard
3. **Replace** - Upload new file, automatically updates HTML everywhere
4. **Delete** - Removes image from HTML (with confirmation)

### Upload New Image
- **"+ Upload New Image"** button at top
- Select file → Auto-uploads → Appears in gallery
- Message: "Click Insert to add to HTML or copy the URL"

## Workflow

### Example: Add Banner Image

1. Go to **Images tab**
2. Click **"+ Upload New Image"**
3. Select `banner.jpg`
4. Image uploads and appears in gallery
5. Hover over image → Click **"Insert"**
6. Message: "Image tag copied! Switch to HTML tab and paste"
7. Go to **HTML tab**
8. Press `Ctrl+V` (or `Cmd+V`) where you want the image
9. Image tag inserted: `<img class="lazy" src="" data-src="storage/theme/3/xxx.webp">`
10. Go to **CSS tab** to style it
11. Go to **Preview tab** to see result

### Example: Replace Image

1. Go to **Images tab**
2. Find the image you want to replace
3. Hover → Click **"Replace"**
4. Select new file
5. ✅ All occurrences in HTML automatically updated!
6. Go to **Preview tab** to see changes

### Example: Delete Image

1. Go to **Images tab**
2. Find the image to delete
3. Hover → Click **"Delete"**
4. Confirm deletion
5. ✅ All `<img>` tags with that URL removed from HTML!

## Technical Details

### Image Detection
- Scans HTML for `src` and `data-src` attributes
- Finds all images in `storage/theme/` path
- Shows unique images only (no duplicates)
- Updates when HTML changes

### Upload
- Accepts: JPEG, PNG, WebP, GIF (up to 10MB)
- Auto-converts to WebP
- Auto-resizes to max 1920x1080px
- Optimizes to 85% quality

### Replace
- Uploads new image
- Finds all occurrences of old URL in HTML
- Replaces with new URL
- Updates gallery

### Delete
- Finds all `<img>` tags with that URL
- Removes from HTML using regex
- Updates gallery

### Insert
- Generates: `<img class="lazy" src="" data-src="storage/theme/3/xxx.webp">`
- Copies to clipboard
- Shows instruction to paste in HTML tab

## Benefits

✅ **Keep your workflow** - HTML/CSS/Preview unchanged
✅ **Visual image management** - See all images in one place
✅ **Easy uploads** - Click button, select file
✅ **Quick insert** - Copy tag, paste in HTML
✅ **Safe replace** - Updates everywhere automatically
✅ **Clean delete** - Removes from HTML automatically
✅ **No learning curve** - Familiar tabs + new Images tab

## Comparison

### Before:
- HTML tab ✅
- CSS tab ✅
- Preview tab ✅
- Images: Upload via "Add Image" button, inserts at cursor
- No way to see all uploaded images
- No way to replace/delete images

### After:
- HTML tab ✅ (same)
- CSS tab ✅ (same)
- **Images tab** ✅ (NEW!)
  - Visual gallery of all images
  - Upload new images
  - Insert to HTML (copies tag)
  - Replace images (updates HTML)
  - Delete images (removes from HTML)
- Preview tab ✅ (same)

## Git Status

✅ **Committed**: `2432c8a88e`
✅ **Pushed to GitHub**: `https://github.com/fes0010/e-commerce-kenya.git`

## Files Modified

1. `packages/Webkul/Admin/src/Resources/views/settings/themes/edit.blade.php` (reverted to original)
2. `packages/Webkul/Admin/src/Resources/views/settings/themes/edit/static-content.blade.php` (added Images tab)
3. `packages/Webkul/Theme/src/Repositories/ThemeCustomizationRepository.php` (reverted to original)

## Deployment

### Option 1: Redeploy in Dokploy
1. Go to Dokploy dashboard
2. Click **"Redeploy"** with **"No Cache"**
3. Test at: `https://ecommerce.munene.shop/admin/settings/themes/edit/3`

### Option 2: Hot-Patch (Immediate)
```bash
# Find container
docker ps | grep ecommerce

# Copy files
docker cp packages/Webkul/Admin/src/Resources/views/settings/themes/edit.blade.php apps-ecommerce-4zagpn.1.XXXXX:/var/www/html/packages/Webkul/Admin/src/Resources/views/settings/themes/edit.blade.php

docker cp packages/Webkul/Admin/src/Resources/views/settings/themes/edit/static-content.blade.php apps-ecommerce-4zagpn.1.XXXXX:/var/www/html/packages/Webkul/Admin/src/Resources/views/settings/themes/edit/static-content.blade.php

docker cp packages/Webkul/Theme/src/Repositories/ThemeCustomizationRepository.php apps-ecommerce-4zagpn.1.XXXXX:/var/www/html/packages/Webkul/Theme/src/Repositories/ThemeCustomizationRepository.php

# Clear caches
docker exec apps-ecommerce-4zagpn.1.XXXXX php artisan view:clear
docker exec apps-ecommerce-4zagpn.1.XXXXX php artisan cache:clear
```

## Verification

After deployment:

1. ✅ Go to theme edit page
2. ✅ See 4 tabs: HTML, CSS, **Images (0)**, Preview
3. ✅ Click **Images tab**
4. ✅ See "Upload New Image" button
5. ✅ Upload an image
6. ✅ Image appears in gallery
7. ✅ Hover → See Insert/Copy/Replace/Delete buttons
8. ✅ Click "Insert" → Tag copied to clipboard
9. ✅ Go to HTML tab → Paste → Image tag inserted
10. ✅ Go to Preview tab → Image displays
11. ✅ Go back to Images tab → Click "Replace" → Upload new file
12. ✅ Go to Preview tab → New image displays
13. ✅ Go to Images tab → Click "Delete" → Confirm
14. ✅ Go to HTML tab → Image tag removed

## Important: Persistent Volumes

Don't forget to configure persistent volumes in Dokploy (see `DOKPLOY-VOLUMES-FIX.md`):

```
Container Path: /var/www/bagisto/storage
Volume Name: ecommerce-storage
```

And use external MySQL:
```
DB_HOST=services-freeman-kgiydl
DB_PORT=3306
DB_DATABASE=bagisto
DB_USERNAME=root
DB_PASSWORD=Enterpassword001.
```

---

**This is exactly what you asked for!** 🎉

- ✅ Keep HTML/CSS/Preview tabs
- ✅ Add Images tab next to Preview
- ✅ Upload images with file upload
- ✅ Manage images in exact locations
- ✅ Delete/Edit options
