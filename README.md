# Filament Table Presets

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ymsoft/filament-table-presets.svg?style=flat-square)](https://packagist.org/packages/ymsoft/filament-table-presets)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/yarmat/filament-table-presets/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/yarmat/filament-table-presets/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/yarmat/filament-table-presets/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/yarmat/filament-table-presets/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/ymsoft/filament-table-presets.svg?style=flat-square)](https://packagist.org/packages/ymsoft/filament-table-presets)

A powerful Filament plugin that allows users to save, manage, and share table configurations including filters, sorting, search queries, and visible columns. Perfect for teams that need to maintain multiple views of their data.

## Features

- 💾 **Save Table States** - Preserve filters, sorting, search, and column visibility
- 👥 **Public & Private Presets** - Share presets with team members or keep them private
- 🎯 **Default Presets** - Set default preset that applies automatically on page load
- 🔄 **Quick Switching** - Toggle between presets with one click via table header actions
- 🔐 **Policy-Based Access Control** - Full authorization support for preset management
- 🔗 **Polymorphic User Relations** - Support for multi-tenancy and different user types
- ⚡ **Auto-Reset Support** - Optionally clear active preset on manual filter/sort changes
- 🌓 **Theme Support** - Full support for light and dark modes
- 🌍 **Translatable** - Built-in support for multiple languages

## Installation

Install the package via Composer:

```bash
composer require ymsoft/filament-table-presets
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="filament-table-presets-migrations"
php artisan migrate
```

Optionally, publish the config file:

```bash
php artisan vendor:publish --tag="filament-table-presets-config"
```

Optionally, publish the views:

```bash
php artisan vendor:publish --tag="filament-table-presets-views"
```

## Configuration

### Panel Registration

Register the plugin in your Filament panel configuration:

```php
use Ymsoft\FilamentTablePresets\FilamentTablePresetPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentTablePresetPlugin::make(),
        ]);
}
```

### Polymorphic User Relationships

If you need to support polymorphic user relationships (useful for multi-tenancy or multiple user types), you need to configure both the plugin and the migration.

**Step 1:** Enable polymorphic relationships in the plugin:

```php
FilamentTablePresetPlugin::make()
    ->hasPolymorphicUserRelationship()
```

**Step 2:** Modify the published migration file to use morphs:

In `database/migrations/create_filament_table_presets_table.php`, uncomment the morph lines and comment out the regular foreign keys:

```php
Schema::create('filament_table_presets', function (Blueprint $table) {
    $table->id();
    $table->string('resource_class');

    // Uncomment for polymorphic relationships:
    $table->morphs('owner');
    // Comment out the regular foreign key:
    // $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();

    // ... rest of the columns
});

Schema::create('filament_table_preset_user', function (Blueprint $table) {
    $table->foreignId('preset_id')->constrained('filament_table_presets')->cascadeOnDelete();

    // Uncomment for polymorphic relationships:
    $table->morphs('user');
    $table->unique(['preset_id', 'user_type', 'user_id']);
    // Comment out the regular foreign key:
    // $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

    // ... rest of the columns
});
```

This allows presets to be associated with different user models (e.g., `App\Models\User`, `App\Models\Admin`, `App\Models\Customer`, etc.).

### Custom Modal Table

Customize the table displayed in the preset management modal:

```php
FilamentTablePresetPlugin::make()
    ->modalTable(MyCustomTableClass::class)
```

### User Model Trait

Add the `HasFilamentTablePresets` trait to your User model:

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Ymsoft\FilamentTablePresets\Traits\HasFilamentTablePresets;

class User extends Authenticatable
{
    use HasFilamentTablePresets;

    // ...
}
```

## Usage

### Basic Setup

To enable table presets on a List page, implement the `HasFilamentTablePresets` interface and use the `WithFilamentTablePresets` trait:

```php
use Filament\Resources\Pages\ListRecords;
use Ymsoft\FilamentTablePresets\Filament\Actions\ManageTablePresetAction;
use Ymsoft\FilamentTablePresets\Filament\Pages\HasFilamentTablePresets;
use Ymsoft\FilamentTablePresets\Filament\Pages\WithFilamentTablePresets;

class ListProducts extends ListRecords implements HasFilamentTablePresets
{
    use WithFilamentTablePresets;

    protected static string $resource = ProductResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->applyDefaultPreset();
    }

    protected function getTableHeaderActions(): array
    {
        return $this->retrieveVisiblePresetActions();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ManageTablePresetAction::make(),
        ];
    }
}
```

### Auto-Reset Active Preset

If you want to automatically deselect the active preset when users manually change filters or sorting, override these methods:

```php
protected function handleTableFilterUpdates(): void
{
    $this->selectedFilamentPreset = null;

    parent::handleTableFilterUpdates();
}

public function updatedTableSort(): void
{
    $this->selectedFilamentPreset = null;

    parent::updatedTableSort();
}
```

This ensures that when users manually adjust table settings, the preset indicator is cleared to avoid confusion.

### Working with Presets

#### Apply Default Preset

```php
public function mount(): void
{
    parent::mount();

    $this->applyDefaultPreset();
}
```

#### Retrieve Visible Preset Actions

Display preset quick-switch buttons in the table header:

```php
protected function getTableHeaderActions(): array
{
    return $this->retrieveVisiblePresetActions();
}
```

#### Manage Presets Action

Add the preset management modal to your page header:

```php
protected function getHeaderActions(): array
{
    return [
        ManageTablePresetAction::make(),
    ];
}
```

## Preset Management

Users can manage their presets through the management modal, which provides:

- **Create New Preset** - Save current table state as a new preset
- **Update Existing** - Sync current table state to an existing preset
- **Toggle Public/Private** - Share presets with other users or keep them private
- **Set as Default** - Mark a preset to load automatically
- **Toggle Visibility** - Show/hide presets from quick-switch buttons
- **Delete Presets** - Remove unwanted presets

## Authorization

The plugin includes a policy for fine-grained access control. Customize `FilamentTablePresetPolicy` to define:

- Who can view presets
- Who can create presets
- Who can update presets
- Who can delete presets
- Who can manage public/private status

## Database Structure

### Tables

- `filament_table_presets` - Stores preset configurations
- `filament_table_preset_user` - Pivot table for user-preset relationships

### Customizing Table Names

Edit the published config file:

```php
return [
    'table_name' => 'filament_table_presets',
    'pivot_table_name' => 'filament_table_preset_user',
];
```

## Translations

The plugin comes with built-in English translations. To add your own language:

**Step 1:** Publish the language files (optional):

```bash
php artisan vendor:publish --tag="filament-table-presets-lang"
```

**Step 2:** Create a new language file in your application:

Create `lang/vendor/filament-table-presets/{locale}/table-preset.php` in your application:

```php
<?php

return [
    'create_preset' => 'Crea un preset',
    'attach_preset' => 'Allega preset',
    'select_preset' => 'Scegli un preset',
];
```

## Testing

```bash
composer test
```

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [YarmaT](https://github.com/YarmaT)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
