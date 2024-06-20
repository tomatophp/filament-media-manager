![Screenshot](https://raw.githubusercontent.com/tomatophp/filament-media-manager/master/arts/3x1io-tomato-media-manager.jpg)

# Filament media manager

[![Latest Stable Version](https://poser.pugx.org/tomatophp/filament-media-manager/version.svg)](https://packagist.org/packages/tomatophp/filament-media-manager)
[![PHP Version Require](http://poser.pugx.org/tomatophp/filament-media-manager/require/php)](https://packagist.org/packages/tomatophp/filament-media-manager)
[![License](https://poser.pugx.org/tomatophp/filament-media-manager/license.svg)](https://packagist.org/packages/tomatophp/filament-media-manager)
[![Downloads](https://poser.pugx.org/tomatophp/filament-media-manager/d/total.svg)](https://packagist.org/packages/tomatophp/filament-media-manager)

Manage your media files using spatie media library with easy to use GUI for FilamentPHP

## Installation

```bash
composer require tomatophp/filament-media-manager
```

after install your package please run this command

```bash
php artisan filament-media-manager:install
```

finally register the plugin on `/app/Providers/Filament/AdminPanelProvider.php`

```php
->plugin(\TomatoPHP\FilamentMediaManager\FilamentMediaManagerPlugin::make())
```

## Publish Assets

you can publish config file by use this command

```bash
php artisan vendor:publish --tag="filament-media-manager-config"
```

you can publish views file by use this command

```bash
php artisan vendor:publish --tag="filament-media-manager-views"
```

you can publish languages file by use this command

```bash
php artisan vendor:publish --tag="filament-media-manager-lang"
```

you can publish migrations file by use this command

```bash
php artisan vendor:publish --tag="filament-media-manager-migrations"
```

## Support

you can join our discord server to get support [TomatoPHP](https://discord.gg/Xqmt35Uh)

## Docs

you can check docs of this package on [Docs](https://docs.tomatophp.com/filament/filament-media-manager)

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security

Please see [SECURITY](SECURITY.md) for more information about security.

## Credits

- [Fady Mondy](https://wa.me/+201207860084)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
