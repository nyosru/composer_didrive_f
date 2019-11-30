<?php

namespace f;

if (!defined('IN_NYOS_PROJECT'))
    die('<h1>Сработала защита функций v1</h1><p>от злостных розовых хакеров.<br>Приготовтесь к DOS атаке (6 поколения на ip-' . $_SERVER["REMOTE_ADDR"] . ') в течении 30 минут.</p>');

class Cash {

    public static $cache = false;
    public static $run = false;

    public static function start() {
        if (self::$run !== true) {
            self::$cache = new \Memcache;
            self::$cache->connect('127.0.0.1', 11211) or die("Could not connect");
            self::$run = true;
        }
    }

    public static function close() {
        if (self::$run === true) {
            $cache->close();
            self::$run = false;
        }
    }

    /**
     * удаляем все ключи
     * @param string $filtr
     */
    public static function deleteKeyPoFilter(string $filtr) {

        self::start();

        $keys = $cache->getAllKeys();

        $regex = $filtr . '.*';
        foreach ($keys as $item) {

            if (isset($item)) {
                $cache->delete($item);
            } 
            //
            elseif (preg_match('/' . $regex . '/', $item)) {
                $cache->delete($item);
            }
            
        }
    }

}
