<?php

namespace Classes;

abstract class Notification implements Interfaces\Templatable
{
    /**
     * Sets suspicious access number to activate anti bruteforce defence
     */
    const NOTIFICATION_SETTINGS = [
        '10' => 'SMS',
        '5' => 'Captcha',
    ];

    /**
     * @var
     */
    public string $confirmName;

    /**
     * @param $spamAttempts
     * @return mixed Notification | bool
     */
    public static function initial($spamAttempts)
    {
        $notificationType = self::getNotificationMethod($spamAttempts);

        if (!empty($notificationType)) return new $notificationType();

        return false;
    }

    /**
     * @param $spamAttempts
     * @return false|string: string | bool
     */
    private static function getNotificationMethod($spamAttempts)
    {
        if ($spamAttempts >= (int)min(array_keys(self::NOTIFICATION_SETTINGS))) {
            foreach (self::NOTIFICATION_SETTINGS as $notificationKey => $notificationMethod) {
                if ($spamAttempts < (int)$notificationKey) {
                    continue;
                } else {
                    $notification = 'Classes\\' . self::NOTIFICATION_SETTINGS[$notificationKey];
                    $_SESSION['notification'] = $notification;
                    break;
                }
            }
            return $notification;
        }

        return false;
    }

    /**
     * @param string $to your message recipient(s). The email address format may be user@example.com or User <user@example.com>. In general, it needs to comply with RFC 2822.
     * @param string $subject your message’s subject
     * @param string $message the body of your message. Lines should be separated with a CRLF (\r\n). Each line should not exceed 70 characters.
     * @return bool
     */
    abstract public function send(string $to, string $subject, string $message): bool;

    /**
     * @param string $answer
     * @return bool
     */
    abstract public function check(string $answer): bool;
}