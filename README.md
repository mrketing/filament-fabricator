# Filament Fabricator

A Filament package for building pages with a page builder.

## Installation

```bash
composer require z3d0x/filament-fabricator
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=filament-fabricator-config
```

## Usage

1. Create layouts in `app/Filament/Fabricator/Layouts`
2. Create page blocks in `app/Filament/Fabricator/PageBlocks`
3. Use the page builder in your forms

## Envoyer Hook Commands

Per risolvere problemi di cache con Filament, aggiungi questi comandi all'hook di Envoyer dopo i comandi esistenti:

```bash
cd {{ release }}
rm bootstrap/cache/config.php
php artisan config:cache
php artisan config:clear
php artisan cache:clear

cp  app/overrides/filament/base.blade.php vendor/filament/filament/resources/views/components/layouts/base.blade.php

cd {{ release }}/vendor/z3d0x
rm -rf filament-fabricator
git clone https://github.com/mrketing/filament-fabricator

cd {{ release }}
php artisan route:cache

# Comandi aggiuntivi per pulire la cache
php artisan view:clear
php artisan optimize:clear
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.