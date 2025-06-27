<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Exception;
use Illuminate\Support\Facades\DB;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(storage_path('app/firebase/firebase_credentials.json'));

        $this->messaging = $factory->createMessaging();
    }

    public function sendToToken(string $token, string $title, string $body, array $data = [])
    {
        if (empty($token)) {
            Log::warning('FCM token is empty. Skipping notification.');
            return;
        }

        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        try {
            $this->messaging->send($message);

            // Update last used time
            DB::table('device_tokens')
                ->where('token', $token)
                ->update([
                    'last_used_at' => now(),
                ]);
        } catch (Exception $e) {
            Log::error('Failed sending notification via FCM: ' . $e->getMessage());

            // Deactivate bad token
            if (str_contains($e->getMessage(), 'invalid') || str_contains($e->getMessage(), 'not registered')) {
                DB::table('device_tokens')
                    ->where('token', $token)
                    ->update(['is_active' => false]);
            }
        }
    }

    public function sendToTokens(array $tokens, string $title, string $body, array $data = [])
    {
        foreach ($tokens as $token) {
            $this->sendToToken($token, $title, $body, $data);
        }
    }
}
