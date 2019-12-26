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
            self::close();
            self::$run = false;
        }
    }

    /**
     * удаляем все ключи
     * @param string $filtr
     */
    public static function deleteKeyPoFilter(array $filtr) {

        self::start();

        $keys = self::$cache->get('keys');
//        $keys[$var] = 1;

        foreach ($keys as $k => $v) {

            $delete_key = null;

            foreach ($filtr as $k1 => $v1) {
                if( strpos( $k , $v1 ) !== false ) {
                    if( $delete_key === true || empty($delete_key) )
                    $delete_key = true;
                }else{
                    $delete_key = false;
                }
            }

            if ( $delete_key === true ) {
                $delete_keys[] = $k;
                self::$cache->delete($k);
                unset($keys[$k]);
            }
            
        }

        //\f\pa($delete_keys,'','','$delete_keys');

        if (!self::$cache->add('keys', $keys, false, 0)) {
            self::$cache->set('keys', $keys, false, 0);
        }

//        return self::$cache->add($var, $data, false, $time);
        return;










        $keys = self::$cache->memcache_get();
        // $keys = self::$cache->getAllKeys();

        // \f\pa($keys);

        return;

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

    /**
     * получаем переменную
     * @param string $var
     * @return type
     */
    public static function getVar(string $var) {
        self::start();
        $vars = self::$cache->get($var);
        return (!empty($vars) ) ? $vars : false;
    }

    /**
     * трём все ключи
     */
    public static function allClear() {
        self::start();
        self::$cache->flush();
    }

    /**
     * добавить значение
     * @param string $var
     * @param type $data
     */
    public static function setVar(string $var, $data, $time = 0) {

        self::start();

        $keys = self::$cache->get('keys');

        $keys[$var] = 1;

        if (!self::$cache->add('keys', $keys, false, 0)) {
            self::$cache->set('keys', $keys, false, 0);
        }

        return self::$cache->add($var, $data, false, $time);
    }

}

/**
 * чистим кеш до нуля
 */
// /index.php?memcache_clear=yes
if (!empty($_GET['memcache_clear']) && $_GET['memcache_clear'] == 'yes') {

    \f\Cash::allClear();
    // \f\Cash::close();
    \f\redirect();
    exit;
}