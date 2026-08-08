<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    /**
     * Send an instant push notification to a specific user via Firebase FCM v1.
     */
    public static function sendToUser(User|string $userOrEmail, string $title, string $body, array $data = []): bool
    {
        $user = is_string($userOrEmail)
            ? User::where('email', $userOrEmail)->first()
            : $userOrEmail;

        if (!$user || empty($user->fcm_token)) {
            Log::info("FCM: User has no fcm_token. Skipping push notification for: " . ($user->email ?? $userOrEmail));
            return false;
        }

        return self::sendRawNotification($user->fcm_token, $title, $body, $data);
    }

    /**
     * Send raw push notification to an FCM device token.
     */
    public static function sendRawNotification(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        $credentialsPath = storage_path('app/firebase/firebase-credentials.json');

        if (!file_exists($credentialsPath)) {
            Log::warning("FCM: Credentials file not found at: {$credentialsPath}");
            return false;
        }

        $credentials = json_decode(file_get_contents($credentialsPath), true);
        $projectId = $credentials['project_id'] ?? null;

        if (!$projectId) {
            Log::warning("FCM: Invalid credentials JSON (missing project_id).");
            return false;
        }

        $accessToken = self::getGoogleAccessToken($credentials);
        if (!$accessToken) {
            Log::error("FCM: Failed to generate Google OAuth2 Access Token.");
            return false;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $payload = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => array_map('strval', $data),
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => [
                        'sound' => 'default',
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'channel_id' => 'adoption_status_channel',
                        'notification_priority' => 'PRIORITY_MAX',
                        'default_sound' => true,
                        'default_vibrate_timings' => true,
                        'visibility' => 'PUBLIC',
                    ],
                ],
            ],
        ];

        $response = Http::withToken($accessToken)
            ->withHeaders(['Content-Type' => 'application/json; UTF-8'])
            ->post($url, $payload);

        if ($response->successful()) {
            Log::info("FCM: Push notification delivered successfully to token: " . substr($fcmToken, 0, 15) . "...");
            return true;
        }

        Log::error("FCM Error: " . $response->body());
        return false;
    }

    /**
     * Generate Google OAuth2 Bearer Access Token using Service Account private key JWT.
     */
    private static function getGoogleAccessToken(array $credentials): ?string
    {
        $now = time();
        $jwtHeader = self::base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));

        $jwtClaim = self::base64UrlEncode(json_encode([
            'iss'   => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'exp'   => $now + 3600,
            'iat'   => $now,
        ]));

        $privateKey = $credentials['private_key'];
        openssl_sign("{$jwtHeader}.{$jwtClaim}", $signature, $privateKey, 'SHA256');
        $jwtSignature = self::base64UrlEncode($signature);

        $assertion = "{$jwtHeader}.{$jwtClaim}.{$jwtSignature}";

        $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $assertion,
        ]);

        if ($tokenResponse->successful()) {
            return $tokenResponse->json('access_token');
        }

        Log::error("Google OAuth2 Token Error: " . $tokenResponse->body());
        return null;
    }

    private static function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
