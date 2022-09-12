<?php

namespace Classes;

use Exception;
use stdClass;

class Forum
{
    /**
     * @var
     */
    private static $error;

    /**
     * @param int $cache_time
     * @return array
     */
    public static function initCache(int $cache_time): array
    {
        $file = strrchr($_SERVER["SCRIPT_NAME"], "/");
        $file = substr($file, 1, -4);
        $cache_file = "cache/$file.txt";

        if (file_exists($cache_file)) {
            $cache = self::loadCache($cache_time, $cache_file);
            $publishedTimes = array_column(array_filter($cache, function ($item) {
                return $item->session_id === $_SESSION['session_id'];
            }), 'time');

            $spamAttempts = count($publishedTimes);
            $lastPublishedTime = array_pop($publishedTimes);
            $firstPublishedTime = array_shift($publishedTimes);
            $bruteForceTimeSeconds = (time() - $firstPublishedTime);
        } else {
            mkdir('cache', 0777, true);
            self::saveDataToSession();

            try {
                $handle = fopen($cache_file, 'a+');
                $test = fwrite($handle, implode('/', $_SESSION['send']) . "\r\n");
                fclose($handle);
            } catch (Exception $exception) {
                (new Forum)->set_error($exception->getMessage());
            }

            $lastPublishedTime = time() - 10;
            $spamAttempts = 0;
            $cache = null;
            $bruteForceTimeSeconds = 0;
        }
        return array($cache_file, $cache, $spamAttempts, $bruteForceTimeSeconds, $lastPublishedTime);
    }

    /**
     * @param int $cache_time
     * @param string $cache_file
     * @return mixed array|bool
     */
    public static function loadCache(int $cache_time, string $cache_file)
    {
        $mass_cache = explode("\r\n", file_get_contents($cache_file));
        $cache = [];

        foreach ($mass_cache as $key => $record) {
            $cache[$key] = new stdClass();
            $tmp = explode("/", $record);

            foreach (['message', 'ip', 'session_id', 'time'] as $paramKey => $param) {

                if (isset($tmp[$paramKey])) {
                    $cache[$key]->{$param} = $tmp[$paramKey];
                } else {
                    unset($cache[$key]);
                }
            }
        }

        $newCache = self::purgeOldCache($cache_file, $cache, $cache_time);
        $cache = (!empty($newCache)) ? $newCache : $cache;

        return $cache;
    }

    /**
     * @param $cache_file
     * @param array $cache
     */
    public static function updateCache($cache_file, array $cache): void
    {
        $cleanedFile = implode("\r\n", array_map(function ($item) {
            return implode('/', (array)$item);
        }, $cache));

        try {
            $handle = fopen($cache_file, 'w');
            $test = fwrite($handle, $cleanedFile);
            fclose($handle);
        } catch (Exception $exception) {
            (new Forum)->set_error($exception->getMessage());
        }
    }


    /**
     * @param $cache_file
     * @param array $cache
     * @param $cache_time
     * @return array|false array|bool
     */
    public static function purgeOldCache($cache_file, array $cache, $cache_time)
    {
        $newCache = array_filter($cache, function ($item) use ($cache_time) {
            if ((time() - $cache_time) < $item->time) {
                return $item;
            }
        });

        if (count($newCache) < count($cache)) {
            self::updateCache($cache_file, $newCache);

            return $newCache;
        }

        return false;
    }

    /**
     * @param $cache
     * @param $cache_file
     * @param $bruteForceTimeSeconds
     * @param $spamAttempts
     * @param $cache_time
     * @param $lastPublishedTime
     */
    public static function publish($cache, $cache_file, $bruteForceTimeSeconds, $spamAttempts, $cache_time, $lastPublishedTime): void
    {
        if (time() - $lastPublishedTime > MINIMAL_PUBLISH_INTERVAL) {
            $messageWithSpaces = WordSplitter::wordBreak(htmlspecialchars($_POST['message']));
            $_SESSION['success'] = "You have successfully sent message to the server. Message\'s text:<br>$messageWithSpaces";
            self::saveDataToSession();
            self::saveDataSessionToCache($cache_file);

            if (!empty($cache)) {
                $newCache = array_map(function ($item) {
                    if ($_SESSION['session_id'] != $item->session_id) {
                        return ($item);
                    }
                }, $cache);

                self::updateCache($cache_file, $newCache);
            }

            unset($_SESSION['spamAttempts']);
            unset($_SESSION['error']);
            unset($_SESSION['message']);

            if (isset($notification)) {
                unset($_SESSION[$notification['confirmName']]);
            }
        } else {
            self::saveDataToSession();
            self::saveDataSessionToCache($cache_file);
            $cache = self::loadCache($cache_time, $cache_file);
            $suspicious = ($spamAttempts > 2) ? " Very suspicious." : "";
            $_SESSION['error'][] = "In a last $bruteForceTimeSeconds seconds you've published a message $spamAttempts times!!!$suspicious";
            $_SESSION['spamAttempts'] = $spamAttempts;
        }
    }

    /**
     * @param $cache_file
     */
    public static function saveDataSessionToCache($cache_file): void
    {
        try {
            $handle = fopen($cache_file, 'a+');
            $test = fwrite($handle, "\r\n" . implode('/', $_SESSION['send']) . "\r\n");
            fclose($handle);
        } catch (Exception $exception) {
            (new Forum)->set_error($exception->getMessage());
        }
    }


    /**
     * @return void
     */
    public static function saveDataToSession(): void
    {
        $_SESSION['send'] = [
            'message' => $_POST['message'],
            'ip' => $_SERVER["REMOTE_ADDR"],
            'session_id' => session_id(),
            'time' => time()
        ];
    }

    /**
     * @return mixed
     */
    public function get_error()
    {
        return self::$error;
    }

    /**
     * @param $mes
     */
    public function set_error($mes)
    {
        self::$error = $mes;
        $file = fopen('log.txt', 'a');
        fwrite($file, "\r\n" . gmdate('Y-m-d H:i:s') . ' ' . $mes);
        fclose($file);
    }
}