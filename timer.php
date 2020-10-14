<?php

namespace f;

//if (!defined('IN_NYOS_PROJECT'))
//    die('<h1>Сработала защита функций v1</h1><p>от злостных розовых хакеров.<br>Приготовтесь к DOS атаке (6 поколения на ip-' . $_SERVER["REMOTE_ADDR"] . ') в течении 30 минут.</p>');

function timer_start($id = 0) {
    \f\CalcMemory::start($id);
    \f\timer::start($id);
}

/**
 * 
 * @param type $id
 * @param type $out
 * @return type
 * на выходе сек - секунд, memory - память Кб
 */
function timer_stop($id = 0, $out = 'str') {
    
    $timer = \f\timer::stop( $out, $id);
    $memory = \f\CalcMemory::stop($id, $out);

// проработать этот вывод данных
    if ($out == 'ar') {
        
        return ['sec' => $timer, 'memory' => $memory ];
    }
    //if ($out == 'str') {
    else {
        
        return $timer . ' сек, ' . $memory . ' kb';
    }
}

class timer {

    public static $start = false;
    public static $last_res = null;
    public static $last_res_ar = [];
    public static $start_ar = [];

    /**
     * начинаем отсчёт
     * @param type $timer_id
     * @param type $return
     * / time - выводит время старта
     * @return type
     */
    public static function start($timer_id = '', $return = false) {
        // echo '<br/>'.__FUNCTION__.' #'.__LINE__;

        if (!empty($timer_id)) {
            self::$start_ar[$timer_id] = microtime(true);

            // echo '<br/>'.self::$start_ar[$timer_id];

            if ($return == 'time') {
                return self::$start_ar[$timer_id];
            } else {
                return;
            }
        } else {
            self::$start = microtime(true);
            // echo '<br/>'.self::$start;
            if ($return == 'time') {
                return self::$start;
            } else {
                return;
            }
        }
    }

    /**
     * завершаем отсчёт
     * @param type $timer_id
     */
    public static function stop($return = 'str', $timer_id = '') {

        // echo '<br/>'.__FUNCTION__.' #'.__LINE__;

        if (!empty($timer_id)) {

            if (!empty(self::$start_ar[$timer_id])) {

                self::$last_res_ar[$timer_id] = microtime(true) - self::$start_ar[$timer_id];

                if ($return == 'str') {

                    // return self::$last_res;
                    // return self::$start .' - '. microtime(true) .' - '. self::$last_res;
                    // echo '<br/>+ '.number_format(self::$last_res_ar[$timer_id], 5, '.', '`');
                    return number_format(self::$last_res_ar[$timer_id], 2, '.', '`');
                } else {
                    // echo '<br/>+ '.self::$last_res_ar[$timer_id];
                    return self::$last_res_ar[$timer_id];
                }
            } else {
                return 00;
            }
        } else {

            self::$last_res = microtime(true) - self::$start;

            if ($return == 'str') {

                // return self::$last_res;
                // return self::$start .' - '. microtime(true) .' - '. self::$last_res;
                // echo '<br/>+ '.number_format(self::$last_res, 5, '.', '`');
                return number_format(self::$last_res, 5, '.', '`');
            } else {
                // echo '<br/>+ '.self::$last_res;
                return self::$last_res;
            }
        }
    }

}

class CalcMemory {

    public static $start = false;
    public static $last_res = null;
    public static $last_res_ar = [];
    public static $start_ar = [];

    /**
     * начинаем отсчёт
     * @param type $timer_id
     * @param type $return
     * / time - выводит время старта
     * @return type
     */
    public static function start(int $timer_id = 0, $return = false) {
        // echo '<br/>'.__FUNCTION__.' #'.__LINE__;

        self::$start[$timer_id] = 0;
        self::$start[$timer_id] = memory_get_usage();
        //echo '<br/>xxx2 '.$startMemory2;
        //echo '<br/>xxx3 '.($startMemory2-$startMemory)/1024/1024;
        //    echo '<br/>xxx' . __LINE__ . ' - ' . round($startMemory / 1024 / 1024, 2);
//        if (!empty($timer_id)) {
//            self::$start_ar[$timer_id] = microtime(true);
//
//            // echo '<br/>'.self::$start_ar[$timer_id];
//
//            if ($return == 'time') {
//                return self::$start_ar[$timer_id];
//            } else {
//                return;
//            }
//        } else {
//            self::$start = microtime(true);
//            // echo '<br/>'.self::$start;
//            if ($return == 'time') {
//                return self::$start;
//            } else {
//                return;
//            }
//        }
    }

    /**
     * завершаем отсчёт
     * @param type $timer_id
     */
    public static function stop(int $timer_id = 0, $return = 'str') {


        if (!empty(self::$start[$timer_id])) {

            $sm2 = 0;
            $sm2 = memory_get_usage();
            // echo '<br/>xxx' . __LINE__ . ' - ' . round($startMemory / 1024 / 1024, 2);
            // return self::$last_res_ar[$timer_id] = round(($sm2 - self::$start[$timer_id]) / 1024, 3);
            self::$last_res_ar[$timer_id] = round(($sm2 - self::$start[$timer_id]) / 1024, 3);

            if ($return == 'str') {

                // return self::$last_res;
                // return self::$start .' - '. microtime(true) .' - '. self::$last_res;
                // echo '<br/>+ '.number_format(self::$last_res_ar[$timer_id], 5, '.', '`');
                return number_format(self::$last_res_ar[$timer_id], 2, '.', '`');
            } else {
                // echo '<br/>+ '.self::$last_res_ar[$timer_id];
                return self::$last_res_ar[$timer_id];
            }
        } else {
            return false;
        }
    }

}
