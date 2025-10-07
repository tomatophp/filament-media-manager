# Testing Guide

This package uses [Pest PHP](https://pestphp.com/) for testing.

## Running Tests

Run all tests:
```bash
composer test
```

Run specific test file:
```bash
./vendor/bin/pest tests/src/MediaManagerPickerTest.php
```

Run tests with coverage:
```bash
./vendor/bin/pest --coverage
```

## Test Structure

### MediaManagerPickerTest.php

Tests for the MediaManagerPicker form component and MediaPicker Livewire component:

- **Component Configuration**
  - Single/multiple selection modes
  - Max/min items validation
  - Collection filtering
  - Action availability

- **Livewire Component**
  - Rendering and initialization
  - Folder navigation
  - Media selection and deselection
  - Max/min items enforcement
  - Search functionality

- **Password Protected Folders**
  - Password prompting
  - Correct password acceptance
  - Incorrect password rejection

- **State Hydration**
  - Loading existing media on edit

- **File Upload**
  - Direct upload from picker modal
  - Auto-selection after upload

### MediaManagerInputTest.php

Tests for the MediaManagerInput form component (Spatie Media Library integration):

- **Component Configuration**
  - Disk configuration
  - Collection settings
  - Max files and file size limits
  - Accepted file types
  - Single/multiple file modes

- **File Upload**
  - Single and multiple file uploads
  - Disk storage verification
  - Custom properties storage

- **Media Retrieval**
  - Get all media from collection
  - Get first media
  - Get media URLs
  - Check media existence

- **Media Deletion**
  - Delete single media
  - Clear collection
  - Cascade delete on model deletion

- **Custom Schema**
  - Custom metadata fields
  - Custom property storage

- **Validation**
  - File size validation
  - File type validation
  - Max files validation
  - Required validation

- **Conversions**
  - Media conversions (if configured)
  - Conversion URL generation

### InteractsWithMediaManagerTest.php

Tests for the InteractsWithMediaManager trait:

- **Media Retrieval**
  - Get all attached media
  - Get media by UUIDs
  - Get media from Spatie collections
  - Filter by field name

- **Media Attachment**
  - Attach media programmatically
  - Prevent duplicate attachments

- **Media Detachment**
  - Detach specific media
  - Detach all media

- **Media Synchronization**
  - Replace all media with new selection

- **Media Existence Check**
  - Check if specific media is attached

- **First Media**
  - Get first media item
  - Handle empty collections

- **Media URLs**
  - Get first media URL
  - Get all media URLs
  - Handle conversions

- **Model Isolation**
  - Ensure media is isolated between different model instances

## Test Models

### Product Model

A test model that uses both Spatie's `InteractsWithMedia` and our `InteractsWithMediaManager` trait:

```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use TomatoPHP\FilamentMediaManager\Traits\InteractsWithMediaManager;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;
    use InteractsWithMediaManager;
}
```

### User Model

A test user model for authentication:

```php
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
```

## Factories

### FolderFactory

Creates test folders with various configurations:

```php
Folder::factory()->create();
Folder::factory()->create(['is_protected' => true, 'password' => 'secret']);
Folder::factory()->create(['parent_id' => $parentFolder->id]);
```

### UserFactory

Creates test users:

```php
User::factory()->create();
User::factory()->create(['email' => 'test@example.com']);
```

## Database Setup

The test suite uses an in-memory SQLite database configured in `TestCase.php`:

```php
#[WithEnv('DB_CONNECTION', 'testing')]
abstract class TestCase extends BaseTestCase
{
    use LazilyRefreshDatabase;
    use WithWorkbench;
}
```

This ensures:
- Fast test execution
- No impact on development database
- Fresh database for each test class

## Test Coverage

The test suite covers:

- ✅ Form component configuration and validation
- ✅ Livewire component functionality
- ✅ File upload and storage
- ✅ Media retrieval and URLs
- ✅ Password protected folders
- ✅ Model trait integration
- ✅ Media attachment/detachment
- ✅ Custom properties and metadata
- ✅ Search and filtering
- ✅ Multi-tenancy and model isolation

## Writing New Tests

When adding new features, follow this structure:

```php
describe('Feature Name', function () {
    it('can do something specific', function () {
        // Arrange
        $model = Model::create([...]);

        // Act
        $result = $model->doSomething();

        // Assert
        expect($result)->toBeSomething();
    });

    it('handles edge case', function () {
        // Test edge cases
    });
});
```

## Continuous Integration

Tests are designed to run in CI environments. Ensure your CI configuration includes:

```yaml
- name: Run Tests
  run: composer test

- name: Run Tests with Coverage
  run: ./vendor/bin/pest --coverage --min=80
```

## Tips for Testing

1. **Use Fakes**: Always use `Storage::fake()` for file operations
2. **Clean State**: Each test should be independent
3. **Descriptive Names**: Test names should clearly describe what they test
4. **Arrange-Act-Assert**: Follow AAA pattern for clarity
5. **Edge Cases**: Don't forget to test error conditions and edge cases

## Common Issues

### Issue: Tests fail with "No such file or directory"

**Solution**: Make sure you're using `Storage::fake()` in your test setup:

```php
beforeEach(function () {
    Storage::fake('public');
    actingAs(User::factory()->create());
});
```

### Issue: "Class not found" errors

**Solution**: Ensure autoloading is configured correctly in `composer.json`:

```json
"autoload-dev": {
    "psr-4": {
        "TomatoPHP\\FilamentMediaManager\\Tests\\": "tests/src"
    }
}
```

Then run: `composer dump-autoload`

### Issue: Database errors

**Solution**: Make sure migrations are loaded in `TestCase.php`:

```php
protected function defineDatabaseMigrations(): void
{
    $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
}
```

## Further Reading

- [Pest PHP Documentation](https://pestphp.com/docs)
- [Laravel Testing](https://laravel.com/docs/testing)
- [Filament Testing](https://filamentphp.com/docs/support/testing)
- [Spatie Media Library Testing](https://spatie.be/docs/laravel-medialibrary/testing)
