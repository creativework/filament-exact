<?php

namespace CreativeWork\FilamentExact\Commands;

use CreativeWork\FilamentExact\Endpoints\WebhookSubscription;
use CreativeWork\FilamentExact\FilamentExactPlugin;
use CreativeWork\FilamentExact\Mail\ExactErrorMail;
use CreativeWork\FilamentExact\Services\ExactService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Log;

class RegisterExactWebhookCommand extends Command
{
    protected $signature = 'exact:register-webhooks';

    protected $description = 'Registreer alle Exact Online webhooks';

    public function handle(ExactService $exactService): void
    {
        $connection = $exactService->connect();

        $plugin = FilamentExactPlugin::get();
        $webhooks = $plugin->getWebhooks();

        foreach ($webhooks as $webhook) {
            $webhookSub = new WebhookSubscription($connection);
            $webhookSub->Topic = $webhook->topic;
            $webhookSub->CallbackURL = route('exact.webhooks.handle', ['slug' => $webhook->slug]);

            try {
                $webhookSub->save();
                $this->info("Webhook geregistreerd: {$webhook->topic} -> {$webhook->slug}");
            } catch (Exception $e) {

                // Ignore if message contains 'Gegeven bestaat reeds'
                if (strpos($e->getMessage(), 'Gegeven bestaat reeds') !== false) {
                    $this->info("Webhook bestaat reeds: {$webhook->topic} -> {$webhook->slug}");

                    continue;
                }

                Log::error('Error registering Exact Online Webooks', [
                    'error' => $e->getMessage(),
                    'topic' => $webhook->topic,
                    'slug' => $webhook->slug,
                ]);

                $recipients = config('filament-exact.notifications.mail.to');
                if ($recipients) {
                    foreach ($recipients as $recipient) {
                        Mail::to($recipient)->send(new ExactErrorMail('Error registering Exact Online webhook: ' . $webhook->topic, $e->getMessage()));
                    }
                }
            }
        }
    }
}
