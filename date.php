<?php

namespace f;

// строчки безопасности
//if (!defined('IN_NYOS_PROJECT'))
//    die('Сработала защита <b>функций MySQL</b> от злостных розовых хакеров.' .
//            '<br>Приготовтесь к DOS атаке (6 поколения на ip-' . $_SERVER["REMOTE_ADDR"] . ') в течении 30 минут.');

/**
 * тащим старт и конец недель что входят в диапазон
 * @param type $date
 * @param type $date_end
 * @return type
 */
function f_date__get_weeks($date, $date_end) {

    $return = [];

    for ($i = 0; $i <= 7; $i++) {
        if (date('w', strtotime($date . ' -' . $i . ' day')) == 1) {
            $date0 = date('Y-m-d', strtotime($date . ' -' . $i . ' day'));
            break;
        }
    }

    for ($i = 1; $i <= 5; $i++) {

        $week_start = date('Y-m-d w', strtotime($date0 . ' +' . $i . ' week -1 week'));
        $week_end = date('Y-m-d w', strtotime($date0 . ' +' . $i . ' week -1 day'));

        $return[] = [$week_start, $week_end];

        // $date0 = date( 'Y-m-d', strtotime($date.' -'.$i.' day') );
        // \f\pa([ $week_start, $week_end ]);

        if ($week_end >= $date2)
            break;
    }

    return $return;
}
