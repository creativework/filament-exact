# ExactOnline Panel for FilamentPHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/creativework/filament-exact.svg?style=flat-square)](https://packagist.org/packages/creativework/filament-exact)
[![Total Downloads](https://img.shields.io/packagist/dt/creativework/filament-exact.svg?style=flat-square)](https://packagist.org/packages/creativework/filament-exact)

## This package is made by [Creative Work](https://creativework.nl)

Hi! We are Creative Work. A company from Buitenpost in the Nederlands.
We are specialized in creating websites and web applications focused on automation for our customers.

## About the package

This package implements a custom ExactQueue to handle the ExactOnline API.
The jobs inside the Queue will be shown in a panel inside your Filament app.

## Why should I use this package?

ExactOnline is a popular accounting software in the Netherlands. This package will help you to integrate ExactOnline with your Filament app.
When errors occur, you can easily see them in the panel and fix them. You can also see the status of the jobs and the progress of the jobs.

The package keeps track of the jobs and the rate limit of the ExactOnline API. When the rate limit is reached, the jobs will be paused and will be resumed when the rate limit is reset.

## Installation

You can install the package via composer:

```bash
composer require creativework/filament-exact
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-exact-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-exact-config"
```

Then add the plugin to your `PanelProvider`

```php
use creativework\FilamentExact\FilamentExactPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(FilamentExactPlugin::make());
}
```

Specify the scheduler in your `bootstrap/app.php`'
```php
->withSchedule(function (Schedule $schedule) {
    if (app()->environment('production')) {
        $schedule->command('exact:process-queue')->everyMinute();
    }
})
```

## Usage
```php
use App\Jobs\ImportInvoiceJob;

ExactQueue::create([
    "job" => ImportInvoiceJob::class,
    "parameters" => [
        "invoice_id" => $invoice->id,
    ],
]);
```

## Credits

- [Jessedev1](https://github.com/Jessedev1)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
