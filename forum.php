<?php

use Classes\Forum;

session_start();
const MINIMAL_PUBLISH_INTERVAL = 10;

require_once 'vendor/autoload.php';
ini_set('display_errors', 1);

unset($_SESSION['error']);

$cache_time = 600; // caching time in seconds

list($cache_file, $cache, $spamAttempts, $bruteForceTimeSeconds, $lastPublishedTime) = Forum::initCache($cache_time);

$notification = Classes\Notification::initial($spamAttempts);

if (!empty($notification)) {
    if (!$notification->check(htmlspecialchars($_POST[$notification->confirmName]))) {
        $_SESSION['error'][] = "You've entered wrong validation code.";
        unset($_SESSION['digit']);
    }
}

Forum::publish($cache, $cache_file, $bruteForceTimeSeconds, $spamAttempts, $cache_time, $lastPublishedTime);

unset($_SESSION['digit']);
header('Location: ' . $_SERVER['HTTP_REFERER']);