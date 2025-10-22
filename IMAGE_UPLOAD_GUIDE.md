# Product & Dish Image Management Guide

## 🖼️ Overview

Your Grillstone POS now has full support for product and dish images with three methods:
1. **Upload local images** (stored on server)
2. **Use external image URLs** (like Unsplash, CDN, etc.)
3. **Delete/replace images**

---

## 📸 How Images Work in the POS

### Current Display
✅ The POS **already displays**:
- Product/Dish **names**
- Product **descriptions**
- Product **prices**
- Product **images** (from `image_url` field)
- Product **categories** (filtered by category)
- Stock levels with badges

### Image Sources
Images are loaded from the `image_url` column in the `products` table:
- **Local uploads**: `/storage/images/products/product_123_timestamp.jpg`
- **External URLs**: `https://images.unsplash.com/photo-xxxxx`

---

## 🔧 API Endpoints for Image Management

### 1. Upload Image File

**Endpoint**: `POST /api/inventory/products/{id}/image`

**Request**: Multipart form data
```javascript
const formData = new FormData();
formData.append('image', fileInput.files[0]);

fetch(`/api/inventory/products/${productId}/image`, {
    method: 'POST',
    body: formData
})
```

**Requirements**:
- Max file size: 2MB
- Allowed formats: JPEG, PNG, JPG, GIF, WEBP
- Files stored in: `storage/app/public/images/products/`
- Accessible at: `/storage/images/products/`

**Response**:
```json
{
    "success": true,
    "image_url": "/storage/images/products/product_22_1234567890.jpg",
    "message": "Image uploaded successfully"
}
```

---

### 2. Update Image URL (External)

**Endpoint**: `PUT /api/inventory/products/{id}/image-url`

**Request**:
```json
{
    "image_url": "https://images.unsplash.com/photo-1604152135912-04a022e23696?w=400"
}
```

**Use Case**: Perfect for using Unsplash, CDN, or other external image services

**Response**:
```json
{
    "success": true,
    "image_url": "https://images.unsplash.com/photo-1604152135912-04a022e23696?w=400",
    "message": "Image URL updated successfully"
}
```

---

### 3. Delete Image

**Endpoint**: `DELETE /api/inventory/products/{id}/image`

**Behavior**:
- Deletes local file from storage (if stored locally)
- Sets `image_url` to NULL in database
- Safe to use with external URLs (just clears the URL)

**Response**:
```json
{
    "success": true,
    "message": "Image deleted successfully"
}
```

---

## 💻 Example Usage in JavaScript

### Upload Local Image
```javascript
async function uploadProductImage(productId, imageFile) {
    const formData = new FormData();
    formData.append('image', imageFile);

    const response = await fetch(`/api/inventory/products/${productId}/image`, {
        method: 'POST',
        body: formData
    });

    const data = await response.json();
    console.log('Image uploaded:', data.image_url);
}

// Usage in file input
document.getElementById('imageInput').addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) {
        uploadProductImage(123, file);
    }
});
```

### Set External Image URL
```javascript
async function setExternalImage(productId, imageUrl) {
    const response = await fetch(`/api/inventory/products/${productId}/image-url`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ image_url: imageUrl })
    });

    const data = await response.json();
    console.log('Image URL updated:', data.image_url);
}

// Usage
setExternalImage(123, 'https://images.unsplash.com/photo-1598103442097-8b74394b95c6?w=400');
```

### Delete Image
```javascript
async function deleteProductImage(productId) {
    const response = await fetch(`/api/inventory/products/${productId}/image`, {
        method: 'DELETE'
    });

    const data = await response.json();
    console.log(data.message);
}

// Usage
deleteProductImage(123);
```

---

## 🎨 Using Unsplash for Jamaican Food Images

### Recommended Unsplash Queries:
```
https://unsplash.com/s/photos/jamaican-food
https://unsplash.com/s/photos/jerk-chicken
https://unsplash.com/s/photos/ackee-and-saltfish
https://unsplash.com/s/photos/caribbean-food
https://unsplash.com/s/photos/rice-and-peas
https://unsplash.com/s/photos/oxtail
https://unsplash.com/s/photos/curry-goat
```

### URL Format:
```
https://images.unsplash.com/photo-[PHOTO_ID]?w=400
```

Add `?w=400` to optimize image size for POS display.

---

## 📂 File Storage Structure

```
storage/
└── app/
    └── public/
        └── images/
            └── products/
                ├── product_22_1729645200.jpg
                ├── product_23_1729645201.png
                └── product_24_1729645202.webp

public/
└── storage/ → symlink to storage/app/public/
```

**Important**: The `php artisan storage:link` command has been run to create the symlink.

---

## 🔄 Current Seeded Data

All 22 Jamaican dishes already have Unsplash images:
- ✅ Ackee & Saltfish
- ✅ Callaloo & Saltfish
- ✅ Jerk Chicken
- ✅ Brown Stew Chicken
- ✅ Curry Chicken
- ✅ Curry Goat
- ✅ Oxtail
- ✅ Escovitch Fish
- ✅ All sides, soups, and combos
- ✅ All 10 beverages

---

## 🛠️ How to Add Images via Inventory Page

### For Dishes (resources/js/pages/Inventory/Dishes.vue):

Add an image upload button/section:

```vue
<template>
  <div class="image-section">
    <img v-if="dish.image_url" :src="dish.image_url" class="w-32 h-32 object-cover rounded-lg">

    <!-- Upload File -->
    <input type="file" ref="imageInput" @change="uploadImage" accept="image/*" class="hidden">
    <button @click="$refs.imageInput.click()" class="btn">Upload Image</button>

    <!-- OR Enter URL -->
    <input v-model="imageUrl" type="url" placeholder="Image URL" class="input">
    <button @click="setImageUrl" class="btn">Set URL</button>

    <!-- Delete -->
    <button v-if="dish.image_url" @click="deleteImage" class="btn-danger">Delete Image</button>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const imageUrl = ref('')

async function uploadImage(event) {
  const file = event.target.files[0]
  if (!file) return

  const formData = new FormData()
  formData.append('image', file)

  const response = await fetch(`/api/inventory/products/${dish.id}/image`, {
    method: 'POST',
    body: formData
  })

  const data = await response.json()
  if (data.success) {
    dish.image_url = data.image_url
    alert('Image uploaded!')
  }
}

async function setImageUrl() {
  const response = await fetch(`/api/inventory/products/${dish.id}/image-url`, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ image_url: imageUrl.value })
  })

  const data = await response.json()
  if (data.success) {
    dish.image_url = data.image_url
    alert('Image URL updated!')
  }
}

async function deleteImage() {
  const response = await fetch(`/api/inventory/products/${dish.id}/image`, {
    method: 'DELETE'
  })

  const data = await response.json()
  if (data.success) {
    dish.image_url = null
    alert('Image deleted!')
  }
}
</script>
```

---

## ✅ Testing the POS

1. **Navigate to POS**: `/pos`
2. **Check displayed items**:
   - All dishes should show with images
   - Names are displayed
   - Prices are shown
   - Categories can be filtered
   - Stock levels visible

3. **Image Fallback**:
   - If image fails to load, POS shows placeholder
   - Handled by `@error` handler in Vue

---

## 🎯 Quick Start Commands

### Re-seed with Jamaican Data (includes images):
```bash
php artisan db:seed --class=JamaicanRestaurantSeeder
```

### Create Storage Link:
```bash
php artisan storage:link
```

### Test Image Upload (cURL):
```bash
# Upload local file
curl -X POST http://localhost:8000/api/inventory/products/22/image \
  -F "image=@/path/to/image.jpg"

# Set external URL
curl -X PUT http://localhost:8000/api/inventory/products/22/image-url \
  -H "Content-Type: application/json" \
  -d '{"image_url":"https://images.unsplash.com/photo-1598103442097-8b74394b95c6?w=400"}'

# Delete image
curl -X DELETE http://localhost:8000/api/inventory/products/22/image
```

---

## 📝 Notes

- **File uploads** are stored permanently until manually deleted
- **External URLs** don't consume server storage
- **Image optimization**: Recommend 400-800px wide for POS display
- **Security**: Only image files allowed (validated by Laravel)
- **Performance**: External URLs load faster (no server processing)

---

## 🚀 Next Steps

1. Add image upload UI to Dishes page
2. Add image upload UI to Products page
3. Add image preview before upload
4. Add image cropping/resizing (optional)
5. Add bulk image upload (optional)

---

**Your POS is now fully equipped with image management! 🎉**
