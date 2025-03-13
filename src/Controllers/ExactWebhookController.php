<?php

namespace CreativeWork\FilamentExact\Controllers;

use App\Http\Controllers\Controller;
use CreativeWork\FilamentExact\FilamentExactPlugin;
use CreativeWork\FilamentExact\Mail\ExactErrorMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Log;

class ExactWebhookController extends Controller
{
    protected FilamentExactPlugin $plugin;

    public function __construct(FilamentExactPlugin $plugin)
    {
        $this->plugin = $plugin::get();
    }

    public function handle(Request $request, string $slug)
    {
        try {
            $webhook = collect($this->plugin->getWebhooks())->firstWhere('slug', $slug);
            if (! $webhook) {
                return response()->json(['error' => 'Webhook not registered'], 404);
            }

            // Retrieve webhook secret from config
            $webhookSecret = config('filament-exact.exact.webhook_secret');
            if (! $webhookSecret) {
                return response()->json(['error' => 'Webhook secret not configured'], 500);
            }

            // Get the request content
            $requestContent = $request->getContent();

            // Authenticate the request
            $authenticate = $webhook->authenticate($requestContent, $webhookSecret);
            if (! $authenticate) {
                return response()->json(['error' => 'Error: Unauthorized webhook'], 201);
            }

            // Decode the payload
            $payload = json_decode($requestContent, true);
            if (! isset($payload['Content'])) {
                return response()->json(['error' => 'Error: Invalid webhook payload'], 201);
            }

            // Validate the payload structure
            $content = $payload['Content'];
            if (! isset($content['Topic'], $content['Action'], $content['Division'], $content['Key'])) {
                return response()->json(['error' => 'Error: Invalid webhook payload'], 201);
            }

            // Trigger the webhook handler
            $webhook->handle($content);

            return response()->json(['message' => 'Webhook received successfully'], 200);
        } catch (Exception $e) {
            Log::error('Error handling Exact Online Webooks', ['error' => $e->getMessage()]);

            $recipients = config('filament-exact.notifications.mail.to');
            if ($recipients) {
                foreach ($recipients as $recipient) {
                    Mail::to($recipient)->send(new ExactErrorMail('Error registering Exact Online webhook', $e->getMessage()));
                }
            }

            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
