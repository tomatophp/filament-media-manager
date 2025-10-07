![Screenshot](https://raw.githubusercontent.com/tomatophp/filament-media-manager/master/arts/fadymondy-tomato-media-manager.jpg)

# Filament media manager

[![Latest Stable Version](https://poser.pugx.org/tomatophp/filament-media-manager/version.svg)](https://packagist.org/packages/tomatophp/filament-media-manager)
[![License](https://poser.pugx.org/tomatophp/filament-media-manager/license.svg)](https://packagist.org/packages/tomatophp/filament-media-manager)
[![Downloads](https://poser.pugx.org/tomatophp/filament-media-manager/d/total.svg)](https://packagist.org/packages/tomatophp/filament-media-manager)

Manage your media files using spatie media library with easy to use GUI for FilamentPHP

## Installation

```bash
composer require tomatophp/filament-media-manager
```

now you need to publish media migration 

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
```

after installing your package, please run this command

```bash
php artisan filament-media-manager:install
```

finally, register the plugin on `/app/Providers/Filament/AdminPanelProvider.php`, if you like to use GUI and Folder Browser.

```php
->plugin(
    \TomatoPHP\FilamentMediaManager\FilamentMediaManagerPlugin::make()
        ->allowSubFolders()
        ->navigationGroup()
        ->navigationIcon()
        ->navigationLabel()
)
```

## Features

- 📁 Manage your media files using spatie media library
- 📂 Create folders and subfolders
- 🔒 Set password for folders with secure access
- 📝 Upload Files with Custom Fields using `->schema()`
- 🤖 Auto Create Folders for Model/Collection/Record
- 🌍 RTL/Multi Language Support
- 🎨 Full Dark Mode Support
- 🖼️ MediaManagerPicker - Browse and select media from folder structure
- ⚡ MediaManagerInput - Direct file upload with Spatie Media Library
- 🔧 InteractsWithMediaManager Trait - Easy model integration
- 📊 Live Preview with thumbnails and file information
- ✅ Selection validation (min/max items)
- 🔄 Auto-save and modal management

## Screenshots

![Folders](https://raw.githubusercontent.com/tomatophp/filament-media-manager/master/arts/folders.png)
![Folder Password](https://raw.githubusercontent.com/tomatophp/filament-media-manager/master/arts/folder-password.png)
![Media](https://raw.githubusercontent.com/tomatophp/filament-media-manager/master/arts/media.png)
![Add Media](https://raw.githubusercontent.com/tomatophp/filament-media-manager/master/arts/add-media.png)
![Add Sub Folder](https://raw.githubusercontent.com/tomatophp/filament-media-manager/master/arts/create-sub-folder.png)
![Preview File](https://raw.githubusercontent.com/tomatophp/filament-media-manager/master/arts/preview-file.png)
![Preview Images](https://raw.githubusercontent.com/tomatophp/filament-media-manager/master/arts/preview-image.png)
![Edit Media](https://raw.githubusercontent.com/tomatophp/filament-media-manager/master/arts/edit-media-meta.png)
![Media Inputs](https://raw.githubusercontent.com/tomatophp/filament-media-manager/master/arts/media-input.png)
![Media Picker Empty State](https://raw.githubusercontent.com/tomatophp/filament-media-manager/master/arts/media-picker-empty.png)
![Media Picker Selected Files](https://raw.githubusercontent.com/tomatophp/filament-media-manager/master/arts/media-picker-selected.png)
![Media Picker Browser](https://raw.githubusercontent.com/tomatophp/filament-media-manager/master/arts/media-picker-browser.png)
![Media Picker After Select](https://raw.githubusercontent.com/tomatophp/filament-media-manager/master/arts/media-picker-after-select.png)
![Media Picker Folders](https://raw.githubusercontent.com/tomatophp/filament-media-manager/master/arts/media-picker-folder.png)
![Media Picker Password Folders](https://raw.githubusercontent.com/tomatophp/filament-media-manager/master/arts/media-picker-password.png)
![Media Picker Selected Files](https://raw.githubusercontent.com/tomatophp/filament-media-manager/master/arts/media-picker-selected-files.png)

## Usage

you can use the media manager by adding this code to your filament component

```php
use TomatoPHP\FilamentMediaManager\Form\MediaManagerInput;

public function form(Schema $schema): Schema
{
    return $schema->components([
        MediaManagerInput::make('images')
            ->disk('public')
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),
            ]),
    ]);
}

```

or you can use Media Library Picker like this

```php
use TomatoPHP\FilamentMediaManager\Form\MediaManagerPicker;

public function form(Schema $schema): Schema
{
    return $schema->components([
         MediaManagerPicker::make('media')
          ->multiple() // or ->single() (default is multiple)
          ->maxItems(5) // Maximum number of items that can be selected
          ->minItems(2) // Minimum number of items required
          ->collection('products') // Filter by collection name
    ]);
}

```

and on your model to manage your attached media 

```php
use TomatoPHP\FilamentMediaManager\Traits\InteractsWithMediaManager;

class Model extends Authenticatable {
    use InteractsWithMediaManager;
} 
```

### MediaManagerPicker Features

- **Multiple/Single Selection**: Use `->multiple()` or `->single()` to control selection mode
- **Item Limits**: Set `->maxItems(n)` and `->minItems(n)` to enforce selection constraints
- **Collection Filter**: Use `->collection('name')` to filter media by collection
- **Password Protected Folders**: Browse secure folders with password verification
- **Live Preview**: See selected items with preview thumbnails, file info, and remove buttons
- **Dark Mode Support**: Fully styled for both light and dark themes
- **Auto-close Modal**: Modal automatically closes after selection with success notification

## Working with Media in Models

### InteractsWithMediaManager Trait

Use the `InteractsWithMediaManager` trait in your models to easily access and manage media attached via MediaManagerPicker:

```php
use TomatoPHP\FilamentMediaManager\Traits\InteractsWithMediaManager;

class Product extends Model
{
    use InteractsWithMediaManager;
}
```

#### Available Methods

```php
// Get all media attached via MediaManagerPicker
$product->getMediaManagerMedia();

// Get media by UUIDs
$product->getMediaManagerMediaByUuids(['uuid-1', 'uuid-2']);

// Get media from Spatie collection (MediaManagerInput)
$product->getMediaManagerInputMedia('images');

// Attach media programmatically
$product->attachMediaManagerMedia(['uuid-1', 'uuid-2']);

// Detach media
$product->detachMediaManagerMedia(['uuid-1']); // Detach specific
$product->detachMediaManagerMedia(); // Detach all

// Sync media (replace all with new)
$product->syncMediaManagerMedia(['uuid-3', 'uuid-4']);

// Check if media exists
$product->hasMediaManagerMedia('uuid-1');

// Get first media item
$product->getFirstMediaManagerMedia();

// Get media URL
$product->getMediaManagerUrl(); // Original
$product->getMediaManagerUrl('thumb'); // With conversion

// Get all media URLs
$product->getMediaManagerUrls(); // All originals
$product->getMediaManagerUrls('thumb'); // All thumbnails
```

#### Usage Example

```php
// In your blade template
@php
    $product = App\Models\Product::find(1);
    $images = $product->getMediaManagerMedia();
@endphp

<div class="product-gallery">
    @foreach($images as $image)
        <img src="{{ $image->getUrl('thumb') }}" alt="{{ $image->name }}">
    @endforeach
</div>

// Get user avatar
@php
    $avatarUrl = auth()->user()->getMediaManagerUrl('avatar') ?? '/default-avatar.png';
@endphp

<img src="{{ $avatarUrl }}" alt="User Avatar">
```

For complete documentation of the trait, see [TRAITS.md](./docs/TRAITS.md).

## Add Custom Preview to selected type on the media manager

you can add custom preview to selected type on the media manager by add this code to your provider

```php
use TomatoPHP\FilamentMediaManager\Facade\FilamentMediaManager;
use TomatoPHP\FilamentMediaManager\Services\Contracts\MediaManagerType;


public function boot() {
     FilamentMediaManager::register([
        MediaManagerType::make('.pdf')
            ->icon('bxs-file-pdf')
            ->preview('media-manager.pdf'),
    ]);
}
```

in your view file you can use it like this 

```php
<div class="m-4">
    <canvas id="the-canvas"></canvas>
</div>

<script src="//mozilla.github.io/pdf.js/build/pdf.mjs" type="module"></script>

<style type="text/css">
    #the-canvas {
        border: 1px solid black;
        direction: ltr;
    }
</style>
<script type="module">
    // If absolute URL from the remote server is provided, configure the CORS
    // header on that server.
    var url = "{{ $media->getUrl() }}";

    // Loaded via <script> tag, create shortcut to access PDF.js exports.
    var { pdfjsLib } = globalThis;

    // The workerSrc property shall be specified.
    pdfjsLib.GlobalWorkerOptions.workerSrc = '//mozilla.github.io/pdf.js/build/pdf.worker.mjs';

    // Asynchronous download of PDF
    var loadingTask = pdfjsLib.getDocument(url);
    loadingTask.promise.then(function(pdf) {

        // Fetch the first page
        var pageNumber = 1;
        pdf.getPage(pageNumber).then(function(page) {
            var scale = 1;
            var viewport = page.getViewport({scale: scale});

            // Prepare canvas using PDF page dimensions
            var canvas = document.getElementById('the-canvas');
            var context = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            // Render PDF page into canvas context
            var renderContext = {
                canvasContext: context,
                viewport: viewport
            };
            var renderTask = page.render(renderContext);
        });
    }, function (reason) {
        // PDF loading error
        console.error(reason);
    });
</script>
```

you can attach global `js` or `css` file to the media manager by add this code to your provider

```php
use TomatoPHP\FilamentMediaManager\Facade\FilamentMediaManager;
use TomatoPHP\FilamentMediaManager\Services\Contracts\MediaManagerType;


public function boot() {
     FilamentMediaManager::register([
        MediaManagerType::make('.pdf')
            ->js('https://mozilla.github.io/pdf.js/build/pdf.mjs'),
            ->css('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.3.136/pdf_viewer.min.css'),
            ->icon('bxs-file-pdf')
            ->preview('media-manager.pdf'),
    ]);
}
```

please note that the `name ` of the component will be the same name of the collection.

## Allow Sub Folders

you can allow create and manage subfolders on your media manager on `/app/Providers/Filament/AdminPanelProvider.php`

```php
->plugins([
    \TomatoPHP\FilamentMediaManager\FilamentMediaManagerPlugin::make()
        ->allowSubFolders()
])
```

## Allow User Access

now you can allow user to access selected folder and restract user to access each other folders if the folder is not public on `/app/Providers/Filament/AdminPanelProvider.php`

```php
->plugin(
    \TomatoPHP\FilamentMediaManager\FilamentMediaManagerPlugin::make()
        ->allowUserAccess()
)
```

now on your user model you can use this trait to allow user to access selected folder

```php

use TomatoPHP\FilamentMediaManager\Traits\InteractsWithMediaFolders;

class User extends Authenticatable
{
    use InteractsWithMediaFolders;
}
```

**NOTE** don't forget to migrate after update the plugin

## Folders API

now you can access your media and folders using API you have 2 endpoints

- `/api/folders` to get all folders
- `/api/folders/{id}` to get folder by id with sub folders and media files

to allow this feature, you need to publish the config file by use this command

```bash
php artisan vendor:publish --tag="filament-media-manager-config"
```

then you can set `api.active` to `true` on the config file

```php
'api' => [
    "active" => true,
],
```

## Publish Assets

you can publish config file by use this command

```bash
php artisan vendor:publish --tag="filament-media-manager-config"
```

you can publish a view file by using this command

```bash
php artisan vendor:publish --tag="filament-media-manager-views"
```

you can publish a language file by using this command

```bash
php artisan vendor:publish --tag="filament-media-manager-lang"
```

you can publish the migrations file by using this command

```bash
php artisan vendor:publish --tag="filament-media-manager-migrations"
```
## Testing

This package includes comprehensive test suites for all major features. Tests are written using [Pest PHP](https://pestphp.com/).

### Running Tests

Run all tests:
```bash
composer test
```

Run a specific test file:
```bash
./vendor/bin/pest tests/src/MediaManagerPickerTest.php
```

Run with coverage:
```bash
./vendor/bin/pest --coverage
```

### Test Coverage

The test suite includes:

- **MediaManagerPickerTest** - Tests for MediaManagerPicker component, folder navigation, password protection, selection validation, and file upload
- **MediaManagerInputTest** - Tests for MediaManagerInput component, file upload, media retrieval, deletion, and custom schema
- **InteractsWithMediaManagerTest** - Tests for the trait methods including attach, detach, sync, and URL generation

For detailed testing documentation, see [tests/README.md](./tests/README.md)

## Code Style

if you like to fix the code style, just use this command

```bash
composer format
```

## PHPStan

if you like to check the code by `PHPStan` just use this command

```bash
composer analyse
```

## Other Filament Packages

Check out our [Awesome TomatoPHP](https://github.com/tomatophp/awesome)

