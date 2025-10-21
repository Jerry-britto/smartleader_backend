<?php

require __DIR__ . '/../vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;


class FirebaseNotificationService
{
    private $messaging;

    public function __construct()
    {
        $serviceAccountPath = __DIR__ . '/FirebaseCredentials.json';
        $factory = (new Factory)->withServiceAccount($serviceAccountPath);
        $this->messaging = $factory->createMessaging();
    }

    /**
     * Send an FCM notification.
     *
     * @param string $fcmToken
     * @param string $title
     * @param string $body
     * @param string|null $imageUrl
     * @return bool
     */
    public function send($fcmToken, $title, $body, $imageUrl = null)
    {
        try {
            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification(Notification::create($title, $body, null, $imageUrl))
                ->withData(['click_action' => 'PROMOTION_SCREEN']);

            $this->messaging->send($message);

            //echo "Notification sent to token: {$fcmToken}\n";
            return true;
        } catch (Exception $e) {
           // echo "FCM Error for token {$fcmToken}: " . $e->getMessage() . "\n";
            return false;
        }
    }
}
