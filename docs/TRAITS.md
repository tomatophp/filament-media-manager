# Media Manager Traits

## InteractsWithMediaManager

The `InteractsWithMediaManager` trait provides convenient methods to interact with media attached to your models via both **MediaManagerPicker** and **MediaManagerInput** (Spatie Media Library).

### Installation

Add the trait to your model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TomatoPHP\FilamentMediaManager\Traits\InteractsWithMediaManager;

class Product extends Model
{
    use InteractsWithMediaManager;

    // ... your model code
}
```

---

## Available Methods

### 1. Get All Media (MediaManagerPicker)

Get all media attached to the model via MediaManagerPicker:

```php
$product = Product::find(1);
$mediaItems = $product->getMediaManagerMedia();

// Get media filtered by field name (if stored in custom properties)
$avatarMedia = $product->getMediaManagerMedia('avatar');
```

**Returns:** `Illuminate\Database\Eloquent\Collection` of Media models

---

### 2. Get Media by UUIDs

Get specific media items by their UUIDs:

```php
$uuids = ['uuid-1', 'uuid-2', 'uuid-3'];
$mediaItems = $product->getMediaManagerMediaByUuids($uuids);
```

**Returns:** `Illuminate\Database\Eloquent\Collection` of Media models

---

### 3. Get Media from Spatie Collection (MediaManagerInput)

Get media from a Spatie Media Library collection:

```php
// Get all media from 'images' collection
$images = $product->getMediaManagerInputMedia('images');

// Get from default collection
$defaultMedia = $product->getMediaManagerInputMedia();
```

**Returns:** `Illuminate\Database\Eloquent\Collection` of Media models

---

### 4. Attach Media

Attach media to the model programmatically:

```php
$uuids = ['uuid-1', 'uuid-2'];
$product->attachMediaManagerMedia($uuids);
```

---

### 5. Detach Media

Detach specific media or all media:

```php
// Detach specific media
$product->detachMediaManagerMedia(['uuid-1', 'uuid-2']);

// Detach all media
$product->detachMediaManagerMedia();
```

---

### 6. Sync Media

Replace all existing media with new media (detach all, then attach new):

```php
$newUuids = ['uuid-3', 'uuid-4', 'uuid-5'];
$product->syncMediaManagerMedia($newUuids);
```

---

### 7. Check if Media Exists

Check if a specific media is attached to the model:

```php
if ($product->hasMediaManagerMedia('uuid-1')) {
    // Media is attached
}
```

**Returns:** `bool`

---

### 8. Get First Media

Get the first media item attached to the model:

```php
$firstMedia = $product->getFirstMediaManagerMedia();

if ($firstMedia) {
    echo $firstMedia->name;
}
```

**Returns:** `Media|null`

---

### 9. Get Media URL

Get the URL of the first media item:

```php
// Get original URL
$url = $product->getMediaManagerUrl();

// Get URL of a specific conversion
$thumbUrl = $product->getMediaManagerUrl('thumb');
```

**Returns:** `string|null`

---

### 10. Get All Media URLs

Get URLs of all media items:

```php
// Get all original URLs
$urls = $product->getMediaManagerUrls();

// Get all thumbnail URLs
$thumbUrls = $product->getMediaManagerUrls('thumb');
```

**Returns:** `array`

---

## Usage Examples

### Example 1: Display Product Images

```php
@php
    $product = App\Models\Product::find(1);
    $images = $product->getMediaManagerMedia();
@endphp

<div class="product-gallery">
    @foreach($images as $image)
        <img src="{{ $image->getUrl('thumb') }}" alt="{{ $image->name }}">
    @endforeach
</div>
```

### Example 2: Display User Avatar

```php
@php
    $user = auth()->user();
    $avatarUrl = $user->getMediaManagerUrl('avatar-thumb') ?? '/default-avatar.png';
@endphp

<img src="{{ $avatarUrl }}" alt="User Avatar" class="avatar">
```

### Example 3: Attach Media in Controller

```php
public function attachFiles(Request $request, Product $product)
{
    $mediaUuids = $request->input('media_uuids');
    $product->attachMediaManagerMedia($mediaUuids);

    return back()->with('success', 'Files attached successfully');
}
```

### Example 4: Sync Media on Update

```php
public function update(Request $request, Product $product)
{
    $product->update($request->only(['name', 'description']));

    // Sync media (replace all existing with new selection)
    if ($request->has('gallery_media')) {
        $product->syncMediaManagerMedia($request->input('gallery_media'));
    }

    return back()->with('success', 'Product updated successfully');
}
```

### Example 5: Check and Display

```php
@if($product->hasMediaManagerMedia($specificUuid))
    <div class="badge">Featured Image Set</div>
@endif

@php
    $featuredImage = $product->getFirstMediaManagerMedia();
@endphp

@if($featuredImage)
    <img src="{{ $featuredImage->getUrl() }}" alt="Featured">
@endif
```

---

## Notes

- All methods that query media bypass the global `folder` scope to ensure you get the correct media
- The `attachMediaManagerMedia()` method uses `updateOrInsert` to prevent duplicates
- Media URLs can be generated with conversions (e.g., `thumb`, `medium`, `large`) if configured in Spatie Media Library
- The trait works with both **MediaManagerPicker** (form component) and **MediaManagerInput** (Spatie collections)

---

## Advanced: Custom Field Names

You can store a field name in the media's custom properties to distinguish between different picker fields:

```php
// When saving, store the field name
$media->setCustomProperty('field_name', 'avatar');
$media->save();

// Later, retrieve only avatar media
$avatarMedia = $user->getMediaManagerMedia('avatar');
```

This is useful when a model has multiple MediaManagerPicker fields and you need to distinguish between them programmatically.
