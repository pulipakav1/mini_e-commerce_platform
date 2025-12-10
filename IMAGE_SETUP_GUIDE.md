# Image Setup Guide

## Image Folders Needed

### 1. **uploads/** folder
- **Location:** Root directory (`/uploads/`)
- **Purpose:** Store product images uploaded via admin panel
- **Permissions:** 755 (must be writable for uploads)
- **Already exists:** ✅ Yes

### 2. **images/categories/** folder  
- **Location:** `/images/categories/`
- **Purpose:** Store category banner/display images
- **Permissions:** 755
- **Naming:** Use category names with underscores
  - `Home_and_Living.jpg`
  - `Cups_and_Bottles.jpg`
  - `Style_Accessories.jpg`
  - `Tulip_Collection.jpg`
  - `Indoor_Plants.jpg`

### 3. **images/** folder (for placeholders)
- **Location:** `/images/`
- **Purpose:** Store placeholder images
- **File:** `category_placeholder.jpg` (if category image not found)
- **File:** `placeholder.png` (if product image not found)

---

## How to Add Images

### **Option 1: Add Product Images via Admin Panel** ✅

1. Login as admin
2. Go to "Manage Products" → "Add New Product"
3. Fill in product details
4. Click "Choose File" under "Product Image"
5. Select image (jpg, jpeg, png, gif)
6. Click "Add Product"
7. Image will be automatically:
   - Uploaded to `uploads/` folder
   - Saved in `images` table in database
   - Displayed on category pages

### **Option 2: Upload Category Images** 📁

1. Create folder: `images/categories/`
2. Upload category images with these exact names:
   - `Home_and_Living.jpg`
   - `Cups_and_Bottles.jpg`
   - `Style_Accessories.jpg`
   - `Tulip_Collection.jpg`
   - `Indoor_Plants.jpg`
3. Set folder permissions to 755

### **Option 3: Manual Product Image Upload** 🔧

If you want to manually add product images:

1. Upload image to `uploads/` folder
2. In phpMyAdmin, insert into `images` table:
   ```sql
   INSERT INTO images (product_id, file_path) 
   VALUES (1, 'uploads/your_image.jpg');
   ```

---

## Current Image System

### **Product Images:**
- Stored in: `uploads/` folder
- Database: `images` table (`file_path` column)
- Format: `uploads/prod_[unique_id].[ext]`
- Displayed on: Category pages, product listings, cart

### **Category Images:**
- Stored in: `images/categories/` folder
- Format: `[Category_Name].jpg` (with underscores for spaces)
- Displayed on: Home page category cards

### **Image Display Locations:**
- ✅ Home page - Category cards
- ✅ Category pages - Product listings
- ✅ Admin product management - View/edit products
- ✅ Cart page - Product thumbnails

---

## Folder Structure

```
/
├── uploads/              (Product images - auto-created)
│   ├── prod_xxx.jpg
│   └── ...
├── images/               (Category images - needs creation)
│   ├── categories/
│   │   ├── Home_and_Living.jpg
│   │   ├── Cups_and_Bottles.jpg
│   │   ├── Style_Accessories.jpg
│   │   ├── Tulip_Collection.jpg
│   │   └── Indoor_Plants.jpg
│   └── category_placeholder.jpg
└── ...
```

---

## Quick Setup Steps

1. **Create category images folder:**
   ```bash
   mkdir -p images/categories
   chmod 755 images/categories
   ```

2. **Upload category images:**
   - Add 5 category images to `images/categories/`
   - Use exact naming convention above

3. **Verify uploads folder exists:**
   - Check `uploads/` folder exists
   - Set permissions to 755
   - Already created automatically when admin adds products

4. **Add placeholder image (optional):**
   - Add `images/placeholder.png` for products without images
   - Add `images/category_placeholder.jpg` for categories without images

---

## Image Requirements

### Product Images:
- **Formats:** JPG, JPEG, PNG, GIF
- **Recommended size:** 800x600px or larger
- **File size:** Under 5MB
- **Aspect ratio:** 4:3 or 16:9

### Category Images:
- **Format:** JPG
- **Recommended size:** 400x300px
- **File size:** Under 1MB

---

## Testing Images

1. **Test product image upload:**
   - Login as admin
   - Add a product with image
   - Check if image appears on category page

2. **Test category images:**
   - Check home page
   - Category cards should show images
   - If not found, shows placeholder

3. **Verify database:**
   - Check `images` table has `file_path` entries
   - Check `uploads/` folder has image files

---

## Troubleshooting

**Images not showing:**
- Check file permissions (755)
- Verify file paths in database
- Check image file exists in folder
- Clear browser cache

**Upload fails:**
- Check `uploads/` folder exists
- Verify folder permissions (755)
- Check PHP upload limits in php.ini
- Check available disk space

**Category images not showing:**
- Verify exact filename matches (case-sensitive)
- Check folder exists: `images/categories/`
- Verify image format is JPG

