<?php

namespace f;

if (!defined('IN_NYOS_PROJECT'))
    die('<h1>Сработала защита функций v1</h1><p>от злостных розовых хакеров.<br>Приготовтесь к DOS атаке (6 поколения на ip-' . $_SERVER["REMOTE_ADDR"] . ') в течении 30 минут.</p>');

class Cash {

    public static $cache = false;
    public static $run = false;
    public static $version = null;

    public static function start() {

        if (self::$run !== true) {
            self::$cache = new \Memcache;
            self::$cache->connect('127.0.0.1', 11211) or die("Could not connect");
            self::$run = true;
            self::$version = self::$cache->getVersion();
//            \f\pa(self::$version);
//            1.5.15 - локалка
//            1.5.20 - сервер
        }
    }

    public static function close() {
        // echo '<br/>'.__FILE__.' '.__LINE__;
        if (self::$run === true) {
            self::close();
            self::$run = false;
        }
    }

    public static function clearCasheFromVars(array $vars) {

        // \f\pa($vars, '', '', 'start clearCasheFromVars');

        for ($nn = 1; $nn <= 10; $nn ++) {

            $var_clear = [];

            for ($n2 = 1; $n2 <= 10; $n2 ++) {
                $kk = 'ajax_cash_delete' . $nn . '_' . $n2;
                if (!empty($vars[$kk])) {
                    // echo '<br/>' . __LINE__ . ' ' . $kk . ' > ' . $vars[$kk];
                    $var_clear[] = $vars[$kk];
                }
            }

//            \f\pa($var_clear);
//            \f\pa($var_clear, '', '', 'self::deleteKeyPoFilter');

            if (!empty($var_clear)) {
                self::deleteKeyPoFilter($var_clear);
            }
        }
    }

    /**
     * удаляем кеши фильтры для удаления лежат в отдельных массивах внутри 1 массива
     * [ [ 'del' => 1 ], [ 'del' => 2 ] ]
     * @param array $filtr
     * @return type
     */
    public static function deleteKeyPoFilterMnogo(array $filtr) {

        if (empty($filtr))
            return \f\end3('пустой входящий массив', false);

        $return = [];
        foreach ($filtr as $v) {
            $return[] = self::deleteKeyPoFilter($v);
        }
        return \f\end3('удалили', true, $return);
    }

    /**
     * удаляем все ключи что содержат все строки из массива $filtr [ 'as' , 'ad' ]
     * @param array $filtr
     */
    public static function deleteKeyPoFilter(array $filtr) {

        // \f\pa($filtr, '', '', 'filtr');

        self::start();

        $delete_keys = [];

        $keys = self::$cache->get('keys');
        // \f\pa($keys, '', '', 'keys');
//        $keys[$var] = 1;

        if (!empty($keys))
            foreach ($keys as $k => $v) {

                $delete_key = true;

                foreach ($filtr as $v1) {

                    if (strpos($k, $v1) !== false && $delete_key === true) {
// if ($delete_key === true || empty($delete_key))
                        if (empty($delete_key))
                            $delete_key = true;
                    } else {
                        $delete_key = false;
                        break;
                    }
                }

                // echo '<br/>' . $k . ' ' . ( $delete_key === true ? 'удаляем' : 'нет' );

                if ($delete_key === true) {

                    $delete_keys[] = $k;
                    // echo '<br/>delete ' . $k;

                    self::$cache->delete($k);

                    // $ee = \f\Cash::$cache->get($k);
                    // \f\pa($ee, '', '', 'date ' . $k);
//                \f\Cash::$cache->delete('jobdesc__hoursonjob_calculateHoursOnJob_2020-01-02_sp1');
//                $ee = \f\Cash::$cache->get('jobdesc__hoursonjob_calculateHoursOnJob_2020-01-02_sp1');
//                \f\pa($ee,'','','2020-01-02 sp1');

                    unset($keys[$k]);
                }
            }

//\f\pa($delete_keys,'','','$delete_keys');

        self::setVar('keys', $keys);

//        if (!self::$cache->add('keys', $keys, false, 0)) {
//            self::$cache->set('keys', $keys, false, 0);
//        }
//        return self::$cache->add($var, $data, false, $time);
        return \f\end3('удалили записей ' . sizeof($delete_keys), true, $delete_keys);

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
        return $vars ?? false;
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

        if ($var == 'keys') {

            $e = self::$cache->set('keys', $data, false, 0);

            if ($e === false) {
                echo '<br/>' . __FILE__ . ' ' . __LINE__;
            }

            if (!self::$cache->set('keys', $data, false, 0))
                self::$cache->add('keys', $data, false, 0);
        } else {

            self::start();
            $keys = self::$cache->get('keys');
            $keys[$var] = 1;

// echo '<br/>v ' . self::$version;

            if (!self::$cache->set('keys', $keys, false, 0)) {
                self::$cache->add('keys', $keys, false, 0);
            }

            if (!self::$cache->set($var, $data, false, $time)) {
                self::$cache->add($var, $data, false, $time);
            }
        }
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