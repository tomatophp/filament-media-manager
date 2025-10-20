# Changelog

All notable changes to `filament-media-manager` will be documented in this file.

## v4.0.3 - 2025-10-20

### 🎯 New Features

#### Collection Names Support
- **Multi-Picker Support** - Added collection name support for multiple pickers on the same page
  - New `collection_name` column in `media_has_models` pivot table
  - Use `->collection('name')` method to specify collection for each picker
  - Each picker maintains its own separate media attachments
  - Collection filtering in all trait methods
  - Backward compatible (null collection name supported)

- **Migration**: `2024_10_21_000000_add_collection_name_to_media_has_models_table.php`
  - Added `collection_name` (nullable string) column
  - Added `responsive_images` (boolean, default false) column
  - Supports rollback with proper column cleanup

- **Updated Methods with Collection Support**:
  ```php
  // Define separate collections for different pickers
  Forms\Components\MediaManagerPicker::make('featured_image')
      ->collection('featured')
      ->single();

  Forms\Components\MediaManagerPicker::make('gallery_images')
      ->collection('gallery')
      ->multiple();

  // Retrieve media by collection
  $product->getMediaManagerMedia('featured');
  $product->getMediaManagerMedia('gallery');
  $product->getMediaManagerUrl('featured');
  $product->getMediaManagerUrls('gallery');
  ```

#### Responsive Images Support
- **Spatie Media Library Integration** - Added responsive images support using Spatie's built-in functionality
  - New `->responsiveImages()` method on MediaManagerPicker
  - Automatic responsive image generation when enabled
  - Responsive images flag stored in pivot table
  - Multiple methods to retrieve responsive image URLs and srcset

- **New Trait Methods for Responsive Images**:
  - `getMediaManagerResponsiveImages(?string $collectionName = null)` - Get media with responsive image data
  - `getMediaManagerSrcset(?string $collectionName = null)` - Get srcset attribute for first media
  - `getMediaManagerSrcsets(?string $collectionName = null)` - Get all srcset attributes
  - `getMediaManagerResponsiveUrls(?string $collectionName = null)` - Get responsive URLs for first media
  - `getAllMediaManagerResponsiveUrls(?string $collectionName = null)` - Get all responsive URLs

- **Usage Example**:
  ```php
  // Enable responsive images
  Forms\Components\MediaManagerPicker::make('hero_image')
      ->collection('hero')
      ->single()
      ->responsiveImages();

  // In Blade templates
  <img src="{{ $model->getMediaManagerUrl('hero') }}"
       srcset="{{ $model->getMediaManagerSrcset('hero') }}"
       alt="Hero Image">

  // Get detailed responsive data
  $responsiveImages = $model->getMediaManagerResponsiveImages('gallery');
  // Returns: ['media' => $media, 'url' => $url, 'responsive_urls' => [...], 'srcset' => '...']
  ```

#### Media Ordering & Reordering
- **Drag & Drop Reordering** - Added drag and drop functionality to reorder media items in MediaManagerPicker
  - Visual drag handle (three bars icon) appears when multiple items are selected
  - Smooth animation during drag (150ms)
  - Ghost effect while dragging (40% opacity)
  - Cursor changes to "grab" on hover over drag handle
  - Only displays for multiple selection mode with 2+ items
  - Proper cleanup and reinitialization of Sortable instances

- **Order Column Support** - Added `order_column` to `media_has_models` pivot table
  - New migration: `2024_10_20_000000_add_order_column_to_media_has_models_table.php`
  - Unsigned integer column with index for performance
  - Nullable to support backward compatibility
  - Automatically managed during attach/sync operations

- **Order Persistence**
  - Media items maintain their order across save/load operations
  - Order preserved when using `attachMediaManagerMedia()`
  - Order preserved when using `syncMediaManagerMedia()`
  - Order reflected in all `getMediaManagerMedia()` calls
  - Order properly hydrated when editing existing records

### 🔧 Bug Fixes

#### MediaManagerPicker Selection Issues
- **Fixed: Media selection not being set to form** - Resolved inconsistent event detail structure
  - Changed from direct data to wrapped structure: `{ media: ... }`
  - Ensures reliable state updates across all scenarios
  - Handles both single and multiple selection modes
  - Fixed race conditions with `isProcessing` flag

- **Fixed: Reordering persistence** - Completely rewrote view logic for reliable drag & drop
  - Server-side ordering now respects state array order
  - Alpine.js state properly synced with Livewire
  - Added `wire:key` for proper Livewire DOM tracking
  - State watcher reinitializes Sortable after re-renders
  - Prevents order from reverting after drag operations

#### InteractsWithMediaManager Trait Updates
- **Collection Name Parameter** - Changed `getMediaManagerUrl()` and `getMediaManagerUrls()` methods
  - Previously: Accepted `$conversion` parameter for image transformations
  - Now: Accepts `$collectionName` parameter to filter by media collection
  - More useful for retrieving specific media groups (e.g., 'gallery', 'thumbnails', 'documents')
  - Breaking change from previous implementation

- **Updated Methods with Ordering**:
  ```php
  // Now returns media in order
  $product->getMediaManagerMedia('gallery');
  $product->getMediaManagerUrl('thumbnails'); // Get first from thumbnails collection
  $product->getMediaManagerUrls('documents'); // Get all URLs from documents collection

  // Order is preserved when syncing
  $product->syncMediaManagerMedia(['uuid-3', 'uuid-1', 'uuid-2']);
  ```

### 📝 Technical Changes

#### Migration Changes
- `media_has_models` table:
  - Added `order_column` (unsigned integer, nullable, indexed)
  - Supports rollback with proper index cleanup

#### View Refactoring
- `resources/views/forms/media-manager-picker.blade.php`
  - Complete rewrite with cleaner Alpine.js logic
  - Removed complex nested x-data scopes
  - Server-side rendering with proper ordering
  - Simplified Sortable.js initialization
  - Added proper instance cleanup on destroy
  - Uses `wire:key` for DOM tracking
  - State watcher for automatic Sortable reinitialization

#### Backend Updates
- `src/Form/MediaManagerPicker.php`
  - Added `$collectionName` property and `collection()` method
  - Added `$generateResponsiveImages` property and `responsiveImages()` method
  - Added `shouldGenerateResponsiveImages()` getter method
  - `afterStateHydrated()`: Now loads and sorts media by `order_column` and filters by `collection_name`
  - `saveRelationshipsUsing()`: Saves media with sequential order values, collection name, and responsive images flag
  - Generates responsive images when flag is enabled

- `src/Traits/InteractsWithMediaManager.php`
  - **Collection Name Support**: All methods updated to accept optional `$collectionName` parameter
  - `getMediaManagerMedia(?string $collectionName)`: Filters by collection name and returns sorted by order
  - `attachMediaManagerMedia(array $uuids, ?string $collectionName)`: Supports collection-specific attachments
  - `detachMediaManagerMedia(?array $uuids, ?string $collectionName)`: Supports collection-specific detachment
  - `syncMediaManagerMedia(array $uuids, ?string $collectionName)`: Collection-aware sync operation
  - `hasMediaManagerMedia(string $uuid, ?string $collectionName)`: Collection-aware existence check
  - `getFirstMediaManagerMedia(?string $collectionName)`: Returns first from specific collection
  - `getMediaManagerUrl(?string $collectionName)`: Gets URL from specific collection
  - `getMediaManagerUrls(?string $collectionName)`: Gets all URLs from specific collection
  - **New Responsive Images Methods**:
    - `getMediaManagerResponsiveImages(?string $collectionName)`: Get media with responsive data
    - `getMediaManagerSrcset(?string $collectionName)`: Get srcset for first media
    - `getMediaManagerSrcsets(?string $collectionName)`: Get all srcsets
    - `getMediaManagerResponsiveUrls(?string $collectionName)`: Get responsive URLs for first media
    - `getAllMediaManagerResponsiveUrls(?string $collectionName)`: Get all responsive URLs

- `src/Livewire/MediaPicker.php`
  - `selectMedia()`: Fixed event detail structure with consistent wrapping

### 🧪 Testing

- ✅ All 97 existing tests passing
- ✅ Order persistence tested across all trait methods
- ✅ Drag and drop functionality verified
- ✅ State synchronization tested
- ✅ Collection filtering tested
- ✅ Backward compatibility maintained

### ⚠️ Breaking Changes

- **InteractsWithMediaManager Trait**:
  - `getMediaManagerUrl(?string $collectionName = null)` - Parameter changed from `$conversion` to `$collectionName`
  - `getMediaManagerUrls(?string $collectionName = null)` - Parameter changed from `$conversion` to `$collectionName`
  - If you were using these methods with conversion parameters, you'll need to update your code
  - Image conversions should now be handled separately using Spatie Media Library's conversion methods

### 📋 Migration Guide

If upgrading from v4.0.0 or v4.0.2:

1. **Run the new migrations**:
   ```bash
   php artisan migrate
   ```
   This will add:
   - `order_column` (if upgrading from v4.0.0)
   - `collection_name` column
   - `responsive_images` column

2. **Update trait method calls** if using conversions:
   ```php
   // Old (v4.0.0)
   $product->getMediaManagerUrl('thumb'); // Got thumbnail conversion

   // New (v4.0.3)
   $product->getMediaManagerUrl('gallery'); // Gets first from 'gallery' collection

   // For conversions, use Spatie directly:
   $media = $product->getFirstMediaManagerMedia('gallery');
   $thumbnailUrl = $media?->getUrl('thumb');
   ```

3. **Using multiple pickers on the same page**:
   ```php
   // Now you can use collection names to separate pickers
   Forms\Components\MediaManagerPicker::make('featured_image')
       ->collection('featured')
       ->single();

   Forms\Components\MediaManagerPicker::make('gallery_images')
       ->collection('gallery')
       ->multiple();

   // Each will maintain separate media attachments
   ```

4. **Using responsive images**:
   ```php
   // Enable responsive images
   Forms\Components\MediaManagerPicker::make('hero_image')
       ->collection('hero')
       ->responsiveImages();

   // Retrieve responsive images
   $srcset = $model->getMediaManagerSrcset('hero');
   ```

5. **Backward Compatibility**:
   - Existing media without `order_column` values will still work (nullable column). Order will be applied on next save.
   - Existing media without `collection_name` will work as before (defaults to null)
   - Responsive images are opt-in via `->responsiveImages()` method

---

## v4.0.0 - 2025-10-07

### <� Major Release - Filament v4 Support

This major version brings full compatibility with Filament v4 along with significant improvements to the MediaManagerPicker component and new model integration features.

### ( New Features

#### MediaManagerPicker Component
- **Enhanced UI/UX**
  - Completely redesigned modal layout with folder navigation on the left and media grid on the right
  - Live preview section showing selected items with thumbnails, file info, and individual remove buttons
  - Success notifications with selection count
  - Auto-close modal after selection with proper state management
  - Full dark mode support using Filament v4 color system

- **Selection Management**
  - Support for single and multiple selection modes via `->single()` and `->multiple()`
  - Added `->maxItems(n)` to limit maximum selections
  - Added `->minItems(n)` to enforce minimum selections
  - Real-time validation with user-friendly warning notifications
  - Selection state preserved when navigating between folders

- **File Upload in Modal**
  - Upload files directly from the MediaManagerPicker modal
  - Auto-select uploaded files after successful upload
  - Respects max items limit during auto-selection
  - Fixed file upload error handling

- **Password Protected Folders**
  - Secure folder access with password verification
  - Password input with reveal toggle
  - Prevented browser autocomplete on password fields
  - Fixed folder opening after correct password entry

- **Search & Navigation**
  - Search across folders and media files
  - Fixed browser autocomplete on search fields
  - Breadcrumb navigation with back button
  - Support for folder hierarchy (model type � collection � instance)

#### InteractsWithMediaManager Trait
- **New Model Trait** for easy media management in Eloquent models
- **11 Helper Methods** including:
  - `getMediaManagerMedia()` - Get all attached media
  - `getMediaManagerMediaByUuids()` - Get specific media by UUIDs
  - `getMediaManagerInputMedia()` - Get Spatie collection media
  - `attachMediaManagerMedia()` - Attach media programmatically
  - `detachMediaManagerMedia()` - Detach media
  - `syncMediaManagerMedia()` - Sync media (replace all)
  - `hasMediaManagerMedia()` - Check if media exists
  - `getFirstMediaManagerMedia()` - Get first media item
  - `getMediaManagerUrl()` - Get media URL with conversions
  - `getMediaManagerUrls()` - Get all media URLs
- **Complete Documentation** in `docs/TRAITS.md`

#### MediaManagerPicker Form Component
- **Empty State Styling** - Filament v4 styled empty state with proper icons and messaging
- **Section Border** - Added styled container around empty state matching Filament's design system
- **Preview List** - Media items displayed as list with thumbnails, file info, and remove buttons
- **Individual Item Removal** - Remove items with confirmation modal

### = Bug Fixes

#### File Upload
- Fixed "FileDoesNotExist" error by using `->storeFiles(false)` and passing files directly to Spatie Media Library
- Proper file handling for TemporaryUploadedFile instances
- Fixed file path issues on upload

#### Selection & State Management
- Fixed media selection only working in secure folders
- Fixed modal not closing after selection
- Fixed selected media not attaching to form
- Fixed nested array error in `whereIn` query by adding `flatten()` filter
- Fixed duplicate event processing with `isProcessing` flag
- Fixed modal remounting instead of closing

#### Event System
- Switched from Livewire events to window CustomEvents for better reliability
- Fixed event propagation across folder navigation
- Handled both event data formats (`{media: array}` and direct array)
- Fixed "Could not find Livewire component in DOM tree" error

#### Folder Navigation
- Fixed files from sub-folders appearing in parent folders
- Implemented proper hierarchical filtering based on folder structure
- Fixed media query to respect folder organization

#### UI/UX Fixes
- Fixed password input autocomplete interference with search field
- Fixed browser email autocomplete on password fields
- Fixed search field autocomplete
- Added unique IDs to form fields to prevent autocomplete conflicts
- Fixed dark mode styling for all components
- Added padding around preview items for better spacing

#### Modal Management
- Fixed modal close by clearing `mountedActions` array properly
- Fixed redirect issue when trying to close modal with button clicks
- Removed ESC key fallback that caused issues
- Proper modal lifecycle management

### = Changes

#### Breaking Changes
- **Filament v4 Required** - This version is compatible with Filament v4 only
- **Updated CSS Classes** - Changed to Filament v4 class structure (e.g., `fi-ta-empty-state`)
- **Dark Mode Pattern** - Updated to use `.classname:where(.dark,.dark *)` pattern
- **CSS Variables** - Now uses Filament v4 CSS variables (e.g., `var(--gray-950)`)

#### Improvements
- **Performance** - Optimized media queries with proper scope handling
- **Code Quality** - Removed debug console.log statements
- **Event Handling** - More reliable event system using native browser events
- **State Synchronization** - Better `$wire.set()` usage for state management
- **Validation** - Improved min/max items validation with clear user feedback

### =� Documentation

- **Updated README.md** with:
  - MediaManagerPicker features and usage
  - InteractsWithMediaManager trait documentation
  - Usage examples for all major features
  - Updated feature list with emojis
- **New TRAITS.md** - Complete guide for the InteractsWithMediaManager trait
- **New CHANGELOG.md** - This comprehensive changelog
- **New tests/README.md** - Comprehensive testing guide

### 🧪 Testing

- **MediaManagerPickerTest** - 30+ test cases covering component configuration, folder navigation, password protection, and file upload
- **MediaManagerInputTest** - 35+ test cases covering file uploads, media retrieval, deletion, and validation
- **InteractsWithMediaManagerTest** - 20+ test cases covering all 11 trait methods
- **Test Infrastructure**: Product model factory, in-memory SQLite, comprehensive documentation

### 🔧 Technical Details

#### Files Modified
- `src/Livewire/MediaPicker.php` - Enhanced with password protection, upload, selection management
- `src/Form/MediaManagerPicker.php` - Added remove action, browse action improvements
- `resources/views/livewire/media-picker.blade.php` - Complete UI overhaul
- `resources/views/forms/media-manager-picker.blade.php` - Preview list, empty state, event handling
- `resources/views/components/media-picker-modal.blade.php` - Initial state support
- `resources/lang/en/messages.php` - Added new translation keys
- `resources/lang/ar/messages.php` - Added Arabic translations

#### Files Created
- `src/Traits/InteractsWithMediaManager.php` - New trait for model integration
- `docs/TRAITS.md` - Trait documentation
- `CHANGELOG.md` - This changelog

#### Files Created (Tests)
- `tests/src/MediaManagerPickerTest.php` - Comprehensive MediaManagerPicker tests
- `tests/src/MediaManagerInputTest.php` - Comprehensive MediaManagerInput tests
- `tests/src/InteractsWithMediaManagerTest.php` - Trait functionality tests
- `tests/src/Models/Product.php` - Test model with media support
- `tests/database/factories/ProductFactory.php` - Product factory for testing
- `tests/database/migrations/2025_10_07_000001_create_products_table.php` - Products table migration
- `tests/README.md` - Testing documentation and guide

### 🙏 Credits

Special thanks to all contributors and users who provided feedback during the development of v4.0.0.

---

## Previous Versions

For changelog of versions before v4.0.0, please refer to the git history or previous documentation.
