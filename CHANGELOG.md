# Changelog

All notable changes to `filament-media-manager` will be documented in this file.

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
