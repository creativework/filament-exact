# Filament Exact - ExactOnline Integration for FilamentPHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/creativework/filament-exact.svg?style=flat-square)](https://packagist.org/packages/creativework/filament-exact)
[![Total Downloads](https://img.shields.io/packagist/dt/creativework/filament-exact.svg?style=flat-square)](https://packagist.org/packages/creativework/filament-exact)

## This package is made by [Creative Work](https://creativework.nl)

Hi! We are Creative Work. A company from Buitenpost in the Nederlands.
We are specialized in creating websites and web applications focused on automation for our customers.

---

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
- 🔄 **Retry-policy** to automatically retry failed jobs.
- 🚀 **Realtime overview** of the queue status.
- 🔐 **Integration with Spatie Laravel Permission** to manage access to the ExactOnline plugin.

### Exact PHP Client
This package is making use of the [Picqer Exact PHP Client](https://github.com/picqer/exact-php-client) to interact with the ExactOnline API. You need to have an ExactOnline account and an API key to use this package.

![Filament Exact](https://raw.githubusercontent.com/Jessedev1/filament-exact/master/docs/filament-exact.png)

---

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
EXACT_ONLINE_WEBHOOK_URI=""
EXACT_ONLINE_CLIENT_DIVISION=""
```

### 4. Add the plugin to your `PanelProvider`
```php
use CreativeWork\FilamentExact\FilamentExactPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentExactPlugin::make()
        ]);
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

### 6. Authorize the application
Navigate to the Filament panel and click on the "Authorize" button in the ExactOnline Resource.
You will be redirected to the ExactOnline login page to authorize the application.
![Authorize Exact](https://raw.githubusercontent.com/Jessedev1/filament-exact/master/docs/filament-exact-authorize-button.png)

---

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
        'webhook_uri' => env('EXACT_ONLINE_WEBHOOK_URI'),
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

---

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
use Log;
use CreativeWork\FilamentExact\Services\ExactService;
use Picqer\Financials\Exact\Item;
use App\Models\Product;

class ImportProductsJob extends ExactQueueJob
{

    public $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function handle(ExactService $service): void
    {
        $connection = $service->getConnection();

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

---

## 📌 Webhooks
This package allows you to **register and handle Exact Online webhooks** in a simple and structured way.

### 1. Create a Webhook Handler
Create a class that extends `ExactWebhook` and implement the `handle` method to process the webhook.
```php
namespace App\Webhooks;

use CreativeWork\FilamentExact\Webhooks\ExactWebhook;

class ItemsWebhook extends ExactWebhook
{

    // Something to identify the webhook
    public string $topic = 'Items';

    // The slug of the webhook
    public string $slug = 'items';

    public function handle($body): void
    {
        // Handle the webhook here
    }

}
```

### 2. Register the Webhook
To register webhooks, call the `webhooks()` method in your `PanelProvider`.
```php
use CreativeWork\FilamentExact\FilamentExactPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentExactPlugin::make()
                ->webhooks([
                    new ItemsWebhook()
                ])
        ]);
}
```

### 3. Run the Webhook Registration Command
After registering the webhooks, you need to run the following command to subscribe to the webhooks with ExactOnline.
```bash
php artisan exact:register-webhooks
```

### 4. Receiving Webhooks
Once registered, Exact Online will send webhooks to your application at the url specified in the `EXACT_ONLINE_WEBHOOK_URI` environment variable.
Make sure the route has a parameter `{slug}` to identify the webhook handler.

For example:
```bash
EXACT_ONLINE_WEBHOOK_URI="https://mydomain.com/exact/webhook/{slug}"
```

### 5. Authorization
To authorize the webhook, you need to provide the `EXACT_ONLINE_WEBHOOK_SECRET` environment variable.

---

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

---

## 🚀 Screenshots

### Queue Overview
The queue overview provides a list of all jobs in the queue, including their status, priority, and progress.
You can filter the queue by status quickly find the job you are looking for. If you want to see more details about a job, you can click on the job to view the details.
![Queue Overview](https://raw.githubusercontent.com/Jessedev1/filament-exact/master/docs/filament-exact-overview.png)

### Queue Job Details
From within this view you can see the progress of the job, the status, and any errors that occurred.
You can also retry the job or delete it from the queue. If you wish to put the job on top of the queue, you can raise the priority of the job.

![Queue Job Details](https://raw.githubusercontent.com/creativework/filament-exact/master/docs/filament-exact-detail.png)

---

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

![Authorize Exact](https://raw.githubusercontent.com/creativework/filament-exact/master/docs/filament-exact-authorize-button.png)

---

## 🧪 Testing
```bash
composer test
```

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
