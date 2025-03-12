# Filament Exact - ExactOnline Integration for FilamentPHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/creativework/filament-exact.svg?style=flat-square)](https://packagist.org/packages/creativework/filament-exact)
[![Total Downloads](https://img.shields.io/packagist/dt/creativework/filament-exact.svg?style=flat-square)](https://packagist.org/packages/creativework/filament-exact)

## This package is made by [Creative Work](https://creativework.nl)

Hi! We are Creative Work. A company from Buitenpost in the Nederlands.
We are specialized in creating websites and web applications focused on automation for our customers.

## About the package
`Filament Exact` is a **FilamentPHP plugin** that makes it easy to integrate **ExactOnline API calls** into your Laravel application. The package provides a **queue system** to process API calls efficiently and displays job statuses and errors inside the Filament admin panel.

### Features
- ✅ **ExactOnline API authentication & token management** automatically.
- ✅ **Rate-limiting** to ensure compliance with ExactOnline's API restrictions.
- ✅ **Job processing & error handling**, allowing developers to focus on business logic.
- ✅ **Error Handling** to ensure that errors are logged and displayed in the panel. You can also rec
- ✅ **Job Progress** to see the progress of the jobs right from your Filament panel.
- ✅ **Priority Queue** to prioritize jobs based on their importance.

### Upcoming features
- 🔈 **Webhooks** to automatically process ExactOnline events.
- 🔄 **Retry-policy** to automatically retry failed jobs.
- 🚀 **Realtime overview** of the queue status.

### Exact PHP Client
This package is making use of the [Picqer Exact PHP Client](https://github.com/picqer/exact-php-client) to interact with the ExactOnline API. You need to have an ExactOnline account and an API key to use this package.

![Filament Exact](https://raw.githubusercontent.com/Jessedev1/filament-exact/master/docs/filament-exact.png)

## 📥 Installation

### 1. Install the Package via composer
```bash
composer require creativework/filament-exact
```

### 2. Publish the assets and run the installer
```bash
php artisan filament-exact:install
```
### 3. Configure Environment Variables
Edit your `.env` file and add the following environment variables:
```dotenv
EXACT_ONLINE_CLIENT_ID=""
EXACT_ONLINE_CLIENT_SECRET=""
EXACT_ONLINE_CLIENT_WEBHOOK_SECRET=""
EXACT_ONLINE_REDIRECT_URI=""
EXACT_ONLINE_CLIENT_DIVISION=""
```

### 4. Add the plugin to your `PanelProvider`
```php
use CreativeWork\FilamentExact\FilamentExactPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(FilamentExactPlugin::make());
}
```
### 5. Scheduler configuration
Specify the scheduler in your `bootstrap/app.php` file:
```php
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        if (app()->environment('production')) {
            $schedule->command('exact:process-queue')->everyMinute();
            $schedule->command('exact:prune')->daily();
        }
    })
    ->create();
```

## ⚙️ Configuration
The package provides a configuration file that allows you to customize the behavior of the package. You can publish the configuration file using:
```bash
php artisan vendor:publish --tag="filament-exact-config"
```

After publishing the configuration file, you can find it in `config/filament-exact.php`.

### Default configuration
```php
return [
    'model' => ExactQueue::class,
    'resource' => ExactQueueResource::class,
    
    'database' => [
        'tables' => [
            'queue' => 'exact_queue',
            'tokens' => 'exact_tokens',
        ],
        'pruning' => [
            'enabled' => true,
            'after' => 30, // days
        ],
    ],

    'notifications' => [
        'mail' => [
            'to' => [],
        ],
    ],

    'exact' => [
        'redirect_uri' => env('EXACT_ONLINE_REDIRECT_URI'),
        'client_id' => env('EXACT_ONLINE_CLIENT_ID'),
        'client_secret' => env('EXACT_ONLINE_CLIENT_SECRET'),
        'division' => env('EXACT_ONLINE_DIVISION'),
        'webhook_secret' => env('EXACT_ONLINE_WEBHOOK_SECRET'),
    ],

    'navigation' => [
        'group' => null,
    ],
];
```

### Explanation
- **model**: The model used to store the queue items.
- **resource**: The resource used to display the queue items in the Filament panel.
- **database.tables**: The database tables used to store the queue items and tokens.
- **database.pruning**: Configuration for pruning old queue items.
- **notifications.mail.to**: The email addresses to send notifications to. (e.g ["errors@creativework.nl", "jesse@creativework.nl"])
- **exact**: Configuration for the ExactOnline API.
- **navigation.group**: The group to add the ExactOnline plugin to in the Filament panel.

## 🚀 Usage

### Adding a Job to the Exact Queue
```php
use App\Jobs\ImportProductsJob;
use CreativeWork\FilamentExact\Models\ExactQueue;

ExactQueue::create([
    "job" => ImportProductsJob::class,
    "parameters" => [
        "invoice_id" => $invoice->id,
    ],
    "priority" => 4, // 1-10 (1 = low, 10 = high)
]);
```
### Create a Custom Job
```php
use CreativeWork\FilamentExact\Jobs\ExactQueueJob;
use Exception;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Log;
use Picqer\Financials\Exact\Connection;
use Picqer\Financials\Exact\Item;
use App\Models\Product;

class ImportProductsJob extends ExactQueueJob
{
    use Dispatchable, SerializesModels;

    public $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function handle(Connection $connection): void
    {
        $itemWrapper = new Item($connection);
        $item = $itemWrapper->find($this->id);
        if (! $item) {
            throw new Exception('Product not found in Exact: '.$this->id);
        }

        // Create a new product in the database
        Product::create([
            'name' => $item->Description,
            'price' => $item->SalesPrice,
            ...
        ]);
    }
}
```

## 🌍 Translations
This package supports multiple languages. You can publish the translation files using:
```bash
php artisan vendor:publish --tag="filament-exact-translations"
```
By default, the package provides translations for:
- 🇬🇧 English
- 🇳🇱 Dutch

To customize the translations, modify the language files inside `resources/lang/vendor/filament-exact/`.
If you would like to contribute translations for other languages, feel free to submit a pull request!


## 🛠 Debugging & Troubleshooting

### 1. Job Stuck in Queue
You can manually process the queue by running the following command:
```bash
php artisan exact:process-queue
```

### 2. View Logs for Errors
```bash
tail -f storage/logs/laravel.log
```

### 3. Getting 401 Unauthorized Errors?
You may need to authorize the application again. You can do this from the Filament panel by clicking on the "Authorize" button in the ExactOnline Resource.

![Authorize Exact](https://raw.githubusercontent.com/Jessedev1/filament-exact/master/docs/filament-exact-authorize-button.png)

## 🔄 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## 🤝 Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## 🔒 Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## 👏 Credits

- [Jessedev1](https://github.com/Jessedev1)
-   [All Contributors](../../contributors)

## 📜 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
