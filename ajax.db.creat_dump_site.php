<?php

//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

ini_set('display_errors', 'On'); // сообщения с ошибками будут показываться
//error_reporting(E_ALL); // E_ALL - отображаем ВСЕ ошибки
error_reporting(-1); // E_ALL - отображаем ВСЕ ошибки

if ($_SERVER['HTTP_HOST'] == 'photo.uralweb.info' || $_SERVER['HTTP_HOST'] == 'yapdomik.uralweb.info' || $_SERVER['HTTP_HOST'] == 'a2.uralweb.info' || $_SERVER['HTTP_HOST'] == 'adomik.uralweb.info'
) {
    date_default_timezone_set("Asia/Omsk");
} else {
    date_default_timezone_set("Asia/Yekaterinburg");
}

define('IN_NYOS_PROJECT', true);

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
//\f\timer::start();
require( $_SERVER['DOCUMENT_ROOT'] . '/all/ajax.start.php' );

//\f\pa('123');
//\f\pa(DR);
//\f\pa( dir_site );
// exec('mysqldump --user=... --password=... --host=... DB_NAME > /path/to/output/file.sql');
// \f\pa($db_cfg);


if( !empty( $_REQUEST['folder'] ) ){
    $dir_site = DS.'sites'.DS.$_REQUEST['folder'].DS;
}else{
    $dir_site = dir_site;
}

//echo '<br/>'.DR;
//echo '<br/>'.dir_site;



$new_dump_dir = DR . $dir_site ;
// \f\pa($new_dump);



$files = scandir(DR . $dir_site);

$latest_file_dt = 0;

$fitetime_now = [];

foreach ($files as $v) {

    if (strpos($v, 'dump.db..') !== false) {

        $ee = filemtime(DR . $dir_site . $v);

        $fitetime_now[$ee] = DR . $dir_site . $v;
        
        if ($ee > $latest_file_dt) {
            $latest_file_dt = $ee;
        }

        // echo '<br/>' . $v;

    }
}

// echo '<Br/>'.date('Y-m-d H:i:s', $latest_file_dt );
krsort($fitetime_now);
//\f\pa($fitetime_now);

if ( 1 == 1 && $latest_file_dt > ( $_SERVER['REQUEST_TIME'] - 3600 * 24 )) {
    die('самый свежий дамп создан менее суток назад, не создаём дампа');
}

// echo __FILE__.' '.__LINE__;

// если база mysql
if ( isset($db_cfg['type']) && $db_cfg['type'] == 'mysql') {

    $name_file_dump = 'dump.db..' . date('Y-m-d_H.i.s') . '..creat..' . ( $db_cfg['type'] ?? '' ) . '..dump.sql';
    $new_dump = $new_dump_dir.$name_file_dump;
    
    exec('mysqldump --user=' . $db_cfg['login'] . ' --password=' . $db_cfg['pass'] . ' --host=' . $db_cfg['host'] . ' ' . $db_cfg['db'] . ' > ' . $new_dump);

    if (file_exists($new_dump)) {

        // echo '<br/>' . __LINE__;
        echo '<br/>создали дамп БД' ;
        // exec('tar -cvf '.$new_dump.'.tar.gz '.$new_dump );
        exec('zip -r -9 -j ' . $new_dump . '.zip ' . $new_dump);

        if (file_exists($new_dump . '.zip') && file_exists($new_dump)) {
            echo '<br/>сжали в архив и удалили оригинал' ;
            unlink($new_dump);
        }
    } else {
        // echo '<br/>' . __LINE__;
    }
    
}
// если pdo sqlite
elseif ( file_exists( DR . $dir_site.'db.sqllite.sl3' ) ) {

//    $name_file_dump = 'dump.db..' . date('Y-m-d_H.i.s') . '..creat..' . ( $db_cfg['type'] ?? '' ) . '..dump.sql';
//    $new_dump = $new_dump_dir.$name_file_dump;
    
    // $name_file_dump = 'dump.db..' . date('Y-m-d_H.i.s') . '..creat..sqlite..dump.sl3';
    $new_dump = DR . $dir_site . 'db.sqllite.sl3';
    
    // exec('mysqldump --user=' . $db_cfg['login'] . ' --password=' . $db_cfg['pass'] . ' --host=' . $db_cfg['host'] . ' ' . $db_cfg['db'] . ' > ' . $new_dump);

//    if ( file_exists( $new_dump )) {

        $new_dump2 = DR . $dir_site . 'dump.db..' . date('Y-m-d_H.i.s') . '..creat..sqlite..dump.sl3';
        
        // echo '<br/>' . __LINE__;
        echo '<br/>создали дамп БД' ;
        // exec('tar -cvf '.$new_dump.'.tar.gz '.$new_dump );
        exec('zip -r -9 -j ' . $new_dump2 . '.zip ' . $new_dump);

//        if (file_exists($new_dump . '.zip') && file_exists($new_dump)) {
//            echo '<br/>сжали в архив и удалили оригинал' ;
            echo '<br/>сжали в архив' ;
//            unlink($new_dump);
//        }
//    } else {
//        // echo '<br/>' . __LINE__;
//    }
    
}


// храним 14 свежих файлов остальные удаляем
$n = 0;
foreach( $fitetime_now as $k => $v ){
    $n++;
    
    if( $n >= 14 && file_exists($v) ){
        echo '<br/>удалили старый файл '.$v;
        unlink($v);
    }
    
}

die();





































//require_once( DR.'/vendor/didrive/base/class/Nyos.php' );
//require_once( dirname(__FILE__).'/../class.php' );
//if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'scan_new_datafile') {
//    scanNewData($db);
//    //cron_scan_new_datafile();
//}
//\f\pa($_REQUEST);
// проверяем секрет
if (
        ( isset($_REQUEST['action']{0}) &&
        (
        $_REQUEST['action'] == 'calc_full_ocenka_day' || $_REQUEST['action'] == 'autostart_ocenka_days' || $_REQUEST['action'] == 'bonus_record' || $_REQUEST['action'] == 'bonus_record_month'
        )
        ) || (
        isset($_REQUEST['id']{0}) && isset($_REQUEST['s']{5}) &&
        \Nyos\nyos::checkSecret($_REQUEST['s'], $_REQUEST['id']) === true
        ) || (
        isset($_REQUEST['id2']{0}) && isset($_REQUEST['s2']{5}) &&
        \Nyos\nyos::checkSecret($_REQUEST['s2'], $_REQUEST['id2']) === true
        ) || (
        isset($_REQUEST['sp']{0}) && isset($_REQUEST['sp_s']{5}) &&
        \Nyos\nyos::checkSecret($_REQUEST['sp_s'], $_REQUEST['sp']) === true
        ) || (
        // action == 'delete_ocenka'
        !empty($_REQUEST['sp']) && !empty($_REQUEST['s']) && !empty($_REQUEST['date']) &&
        \Nyos\nyos::checkSecret($_REQUEST['s'], $_REQUEST['sp'] . $_REQUEST['date']) === true
        )
) {
    
}
//
else {



//    $e = '';
//    foreach ($_REQUEST as $k => $v) {
//        $e .= '<Br/>' . $k . ' - ' . $v;
//    }

    $e = \f\pa($_REQUEST, 'html2');

    f\end2('Произошла неописуемая ситуация #' . __LINE__ . ' обратитесь к администратору'
            . '<br/>'
            . '<br/>'
            . 'Попробуйте обновить страницу и повторить действие'
            . '<br/>'
            . '<br/>'
            . $e // . $_REQUEST['id'] . ' && ' . $_REQUEST['secret']
            , 'error');
}


//require_once( $_SERVER['DOCUMENT_ROOT'] . '/0.all/sql.start.php');
//require( $_SERVER['DOCUMENT_ROOT'] . '/0.site/0.cfg.start.php');
//require( $_SERVER['DOCUMENT_ROOT'] . DS . '0.all' . DS . 'class' . DS . 'mysql.php' );
//require( $_SERVER['DOCUMENT_ROOT'] . DS . '0.all' . DS . 'db.connector.php' );
//
// сохраняем измененеия и распространяем если нужно на другие дни недели
//
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit_norms') {

    ob_start('ob_gzhandler');

    echo '<br/>для показа обновлённых значений <a href="" >обновите страницу</a><br/>';

    $now_month = ceil(date('m', strtotime($_REQUEST['date'])));

    // \f\pa($_REQUEST);

    $new_data = array(
        // 'vuruchka' => $_REQUEST['vuruchka'],
        'vuruchka_on_1_hand' => $_REQUEST['vuruchka_on_1_hand'],
        'time_wait_norm_cold' => $_REQUEST['time_wait_norm_cold'],
        'time_wait_norm_hot' => $_REQUEST['time_wait_norm_hot'],
        'time_wait_norm_delivery' => $_REQUEST['time_wait_norm_delivery'],
        'procent_oplata_truda_on_oborota' => $_REQUEST['procent_oplata_truda_on_oborota'],
        'kolvo_hour_in1smena' => $_REQUEST['kolvo_hour_in1smena']
    );

    $last_day = date('t', strtotime($_REQUEST['date']));
    $year_month = substr($_REQUEST['date'], 0, 8);
    $save_day = [];

    for ($i = 1; $i <= $last_day; $i++) {

        $time = strtotime($year_month . $i);
        $dn = date('w', $time);

        if (isset($_REQUEST['copyto'][$dn])) {

            // день подходящий по дню недели если их выбирали
            // echo ' '.$dn.' ';
            $save_day[date('Y-m-d', $time)] = 1;
        }
    }

    // текущий день
    $save_day[$_REQUEST['date']] = 1;

    $for_sql = '';

    $norms = \Nyos\mod\items::getItemsSimple($db, 'sale_point_parametr');

    foreach ($norms['data'] as $k1 => $v1) {

        if (isset($v1['dop']['sale_point']) && $v1['dop']['sale_point'] == $_REQUEST['sp'] && isset($save_day[$v1['dop']['date']])) {

            $for_sql .= (!empty($for_sql) ? ' OR ' : '' ) . ' `id` = \'' . $v1['id'] . '\' ';
            // \Nyos\mod\items::deleteItems($db, $e, $module_name, $data_dops);
        }
    }

    $ocenki = \Nyos\mod\items::getItemsSimple($db, 'sp_ocenki_job_day');

    foreach ($ocenki['data'] as $k1 => $v1) {

        if (isset($v1['dop']['sale_point']) && $v1['dop']['sale_point'] == $_REQUEST['sp'] && isset($save_day[$v1['dop']['date']])) {

            $for_sql .= (!empty($for_sql) ? ' OR ' : '' ) . ' `id` = \'' . $v1['id'] . '\' ';
            // \Nyos\mod\items::deleteItems($db, $e, $module_name, $data_dops);
        }
    }

    if (!empty($for_sql)) {
        $sql = 'UPDATE `mitems` SET `status` = \'delete\' WHERE ( `module` = \'sale_point_parametr\' OR `module` = \'sp_ocenki_job_day\' ) AND ( ' . $for_sql . ' ) ';
        //\f\pa($sql);
        $ff = $db->prepare($sql);
        $ff->execute();
    }






    $indbs = [];

    echo 'Записали нормы по датам:';

    foreach ($save_day as $k => $v) {

        $in = $new_data;
        $in['date'] = $k;

        echo ' ' . $k;

        $in['sale_point'] = $_REQUEST['sp'];

        //$indbs[] = $in;
        // \f\pa($in);
        $e = \Nyos\mod\items::addNewSimple($db, 'sale_point_parametr', $in);
        // \f\pa($e);
    }

    //\f\pa($indbs);

    $r = ob_get_contents();
    ob_end_clean();

    \f\end2($r, true);
}



// пишем бонусы по зарплате за месяц по 1 точке
elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'bonus_record_month') {

    if (empty($_REQUEST['date']))
        \f\end2('нет даты', false);

    if (!empty($_REQUEST['sp']) && is_numeric($_REQUEST['sp'])) {
        
    } else {
        \f\end2('не определена точка продаж', false);
    }

    \f\timer::start(3);

    $date_start = date('Y-m-00', strtotime($_REQUEST['date']));
    $date_finish = date('Y-m-d', strtotime($date_start . ' +1 month -1 day'));

    $e = [];

    for ($n = 0; $n <= 32; $n++) {

//    $date_start = date('Y-m-00', strtotime($_REQUEST['date']) );
//    $date_finish = date('Y-m-d', strtotime($date_start.' +1 month -1 day') );

        $date = date('Y-m-d', strtotime($date_start . ' +' . $n . ' day'));

        if ($date >= $date_finish)
            break;

        // echo '<br/>'.$date;

        $e2 = \Nyos\mod\JobDesc::creatAutoBonus($db, $_REQUEST['sp'], $date);

        // \f\pa($e2);

        if (isset($e2['data']['adds']))
            foreach ($e2['data']['adds'] as $k => $v) {
                $e['datas'][] = $v;
            }

        // \f\pa($ee,'','','$ee создание автобонусов');
    }

    // echo \f\timer::stop('str', 3);
    $e['timer'] = \f\timer::stop('str', 3);
    $e['kolvo'] = sizeof($e['datas']);

    // \f\pa($e);

    \f\end2('ok', true, $e);
}


// пишем бонусы по зарплате за день по 1 точке
elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'bonus_record') {


    if (empty($_REQUEST['date']))
        \f\end2('нет даты', false);

    if (!empty($_REQUEST['sp']) && is_numeric($_REQUEST['sp'])) {
        
    } else {
        \f\end2('не определена точка продаж', false);
    }

    // \f\timer::start(3);


    $ee = \Nyos\mod\JobDesc::creatAutoBonus($db, $_REQUEST['sp'], $_REQUEST['date']);
    // \f\pa($ee,'','','$ee создание автобонусов');

    \f\end2('ok', true, $ee);

    // echo \f\timer::stop('str', 3);
}
// считаем автооценку дня и пишем
elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'autostart_ocenka_days') {


    \Nyos\mod\items::$join_where = ' INNER JOIN `mitems-dops` mid '
            . ' ON mid.id_item = mi.id '
            . ' AND mid.name = \'date\' '
            . ' AND mid.value_date <= :ds '
            . ' AND mid.value_date >= :df '
    ;
    \Nyos\mod\items::$var_ar_for_1sql[':ds'] = date('Y-m-d');
    \Nyos\mod\items::$var_ar_for_1sql[':df'] = date('Y-m-d', $_SERVER['REQUEST_TIME'] - 40 * 24 * 3600);
    $_ocenki = \Nyos\mod\items::get($db, \Nyos\mod\JobDesc::$mod_ocenki_days);
    // \f\pa($_ocenki, '','','$_ocenki');
    $ocenki_now = [];
    foreach ($_ocenki as $k => $v) {
        // $ocenki_now[$v['sale_point']][$v['date']] = $v;
        $ocenki_now[$v['sale_point']][$v['date']] = 1;
    }
    // \f\pa($ocenki_now, '', '', '$ocenki_now');
    // $_sps = \Nyos\mod\items::getItemsSimple($db, \Nyos\mod\JobDesc::$mod_sale_point);
    $_sps = \Nyos\mod\items::get($db, \Nyos\mod\JobDesc::$mod_sale_point);
    // \f\pa($_sps, 2);
//    $kk = \f\Cash::getVar('keys');
//    \f\pa($kk, 2, '', 'kk');

    $temp_var = 'autoocenka_errors';

    $temp_ar = \f\Cash::getVar($temp_var);
    // \f\pa($temp_ar, 2, '', 'temp_ar');

    \f\timer_start(7);

    for ($i = 1; $i <= 40; $i++) {

        $new_date = date('Y-m-d', $_SERVER['REQUEST_TIME'] - $i * 24 * 3600);

        foreach ($_sps as $sp) {
            // \f\pa($sp);

            $timer = \f\timer_stop(7, 'ar');

            if ($timer['sec'] > 25)
                break;

            if (empty($ocenki_now[$sp['id']][$new_date])) {


                $u = [
                    'action' => 'calc_full_ocenka_day',
                    // 'id' => '1_26',
                    'id' => $sp['id'] . '_26',
                    // 'id2' => '1',
                    'id2' => $sp['id'],
                    // 's' => '8c46f1f47a59fbacefdf5939673c2dd0',
                    's' => md5($sp['id'] . '_26'),
                    // 's2' => '43c891d7623305fabb1e3d809a78c0a9',
                    's2' => md5($sp['id']),
                    'show_timer' => 'da',
                    'sp' => $sp['id'],
                    'date' => $new_date,
                ];

                if (isset($temp_ar[$u['sp']][$u['date']]))
                    continue;

                // echo '<br/>' . $sp['id'] . ' + ' . $new_date;

                if ($curl = curl_init()) { //инициализация сеанса
                    // $curl
                    // curl_setopt($curl, CURLOPT_URL, 'http://webcodius.ru/'); //указываем адрес страницы
                    //указываем адрес страницы
                    curl_setopt($curl, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . '/vendor/didrive_mod/jobdesc/1/didrive/ajax.php?' . http_build_query($u));
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    // curl_setopt ($curl, CURLOPT_POST, true);
                    // curl_setopt ($curl, CURLOPT_POSTFIELDS, "i=1");
                    curl_setopt($curl, CURLOPT_HEADER, 0);
                    $result = curl_exec($curl); //выполнение запроса
                    curl_close($curl); //закрытие сеанса
                }
                // echo '</div>';

                $r1 = json_decode($result, true);

                // \f\pa($result);
                // \f\pa($r1,'','','$r1');
                // \f\pa($r1, '', '', '$r1');

                if ($r1['status'] == 'error') {

                    $temp_ar[$u['sp']][$u['date']] = $r1;
                    // $temp_ar[$u['sp']][$u['date']] = 1;
                    echo '<br/>' . $sp['id'] . ' E ' . $new_date . ' ошибка';
                } else {

                    // echo '<br/>' . __FILE__ . ' ' . __LINE__;
                    // echo '<br/>нет ошибок';
                    // echo '<div style="border: 1px solid green; margin: 10px; padding: 10px; " >';
                    // \f\pa($result, '', '', 'result');
                    // \f\pa($r1, '', '', 'result 1');
                    // echo '</div>';
                    $temp_ar[$u['sp']][$u['date']] = 'ok';
                    echo '<br/>' . $r1['data']['sp'] . ' > ' . $r1['data']['date'] . ' = вр ' . $r1['data']['ocenka_time'] . ' / руки ' . $r1['data']['ocenka_naruki'] . ' / ' . $r1['data']['ocenka'];
                }


                // echo '</div>';
                // die();
            } else {
//                echo '<br/>'.$sp['id'].' + '.$new_date;
//                \f\pa($ocenki_now[$sp['id']][$new_date]);
            }
        }
    }

    \f\Cash::setVar($temp_var, $temp_ar, 60 * 60 * 2);


    die('the end');

    //foreach( )













    /**
     * лог ошибок, трём раз в сутки в 4 утра
     */
    $cash_file_errors = DR . '/sites/' . $vv['folder'] . '/log.clear-24.json';
// массив с ошибками что были найдены ранее
    $log_errors = ( file_exists($cash_file_errors) ? json_decode(file_get_contents($cash_file_errors), true) : [] );

// echo 'ищем дни без оценки action = ' . $_REQUEST['action'] . '<hr>';

    $tt = \Nyos\mod\JobDesc::getDaysOcenkaNo($db);
// \f\pa($tt['data'], 2, '', '\Nyos\mod\JobDesc::getDaysOcenkaNo');
// exit;

    $result1 = [];

// повторы если указан $_REQUEST['povtorov']
    $povtorov = $_REQUEST['povtorov'] ?? 20;

    $nn1 = 0;
// echo '<hr>' . __LINE__ . '<hr>';
// echo '<fieldset><legend>получили данные начинаем шарить по тем каких нет</legend>';

    foreach ($tt['data'] as $date => $sps) {

        if ($nn1 >= $povtorov)
            break;

// echo '<br/>' . __FILE__ . ' ' . __LINE__;
// echo '<br/>' . $sp . ' ' . $date;

        foreach ($sps as $sp => $v) {

            if (!empty($v)) {
// \f\pa($v);
// echo '<br/>skip string';
                continue;
            }

            if ($nn1 >= $povtorov)
                break;

            if (isset($log_errors[$date][$sp]))
                continue;

            $r2 = [
                'sp' => $sp,
                'date' => $date,
                'res_type' => false
            ];

//            echo '<fieldset><legend>' . __FILE__ . ' ' . __LINE__.'</legend>'
//            .'<br/>' . $sp . ' ' . $date
//            .'</fieldset>';
// запуск через гет
            if (1 == 2) {

                $for_get = [
                    'action' => 'calc_full_ocenka_day',
                    // 'date' => '2019-11-05',
                    'date' => $date,
                    // 'sp' => '2229'
                    'sp' => $sp
                ];

                $uri = 'https://yapdomik.uralweb.info/vendor/didrive_mod/jobdesc/1/didrive/ajax.php?' . http_build_query($for_get);
//            // echo '<Br/>' . $uri;
//
                $ee = file_get_contents($uri);
                $ee1 = json_decode($ee, true);

//            // \f\pa($ee1, 2, '', '$ee');
//
//            $ee1['uri0'] = $uri;
//            $ee1['sp0'] = $sp;
//            $ee1['date0'] = $date;
                echo '<br/>' . __FILE__ . ' #' . __LINE__;
                \f\pa($ee1, 2, '', '$ee1 результ оценки дня (вызов страницы)');
            }

// запуск через функцию
            else {

                $r2['res_type'] = 'func';

                try {

//                     echo '<fieldset><legend>' . __FILE__ . ' #' . __LINE__ . '</legend>';
                    $ee1 = \Nyos\mod\jobdesc::calculateAutoOcenkaDays($db, $sp, $date);
//                    \f\pa($ee1, 2, '', '$ee1 результ оценки дня 1 (функция)');

                    if (!empty($ee1['data']['error']) && !empty($ee1['data']['code'])) {
                        $r2['status'] = 'error';
                    } else {
                        $r2['status'] = 'ok';
                    }

                    $r2['res'] = $ee1['data'] ?? 'x';

//                     echo '</fieldset>';
                }

//
                catch (\Exception $ex) {

                    echo '<br/>' . __FILE__ . ' ' . __LINE__;
                }
            }

//            if (!empty($ee1['error'])) {
//                $result1[] = $ee1;
//            }


            $result1[] = $r2;

            $nn1++;

// echo '<br/><hr>nn1 ' . $nn1 . ' ' . __LINE__;
        }


// echo '<br/><hr>nn1 ' . $nn1 . ' ' . __LINE__;
    }
//    $e = \Nyos\mod\items::getItemsSimple($db, 'sp_ocenki_job_day');
//    \f\pa($e,2,'','$e');
// echo '</fieldset>';
// echo '<hr>' . __LINE__ . '<hr>';
// echo '<br/>' . __LINE__ . '<div style="border: 2px solid orange; padding: 20px; max-height: 400px; overflow: auto;" >';
// \f\pa($result1, 2, '', '$result1');
// echo '</div>';
// \f\pa($log_errors, 2);

    $for_msg = '';

    foreach ($result1 as $k => $v) {

        echo '<fieldset><legend>' . $_sps['data'][$v['sp']]['head'] . ' > ' . $v['date'] . '</legend>';

        $for_msg .= $_sps['data'][$v['sp']]['head'] . ' > ' . $v['date'] . PHP_EOL;

        if (isset($v['status']) && $v['status'] == 'error') {

            $for_msg .= 'ошибка: ' . $v['res']['error'] . ' #' . $v['res']['code'] . PHP_EOL;

            echo '<Br/>' . __LINE__;
            echo '<Br/>' . $v['res']['error'] . ' #' . $v['res']['code'];
            $log_errors[$v['date']][$v['sp']] = ['msg' => $v['res']['error'], 'code' => $v['res']['code']];
        } else {
            echo '<Br/>' . __LINE__;
            echo '<Br/>результ норм' .
            ' / sp - ' . $v['sp'] .
            ' / date - ' . $v['date'] .
            ' / часов - ' . $v['res']['hours'] .
            ' / оценка - ' . $v['res']['ocenka'] .
            ' / оценка время - ' . $v['res']['ocenka_time'] .
            ' / оценка на руки - ' . $v['res']['ocenka_naruki'];

            $for_msg .= 'оценка выставлена: ' .
                    ' общая: ' . $v['res']['ocenka'] .
                    ' / время: ' . $v['res']['ocenka_time'] .
                    ' / на руки: ' . $v['res']['ocenka_naruki'] . PHP_EOL;
        }

        echo '</fieldset>';
    }

    require_once DR . dir_site . 'config.php';

// \f\pa($vv['admin_ajax_job']);

    if (1 == 1 && class_exists('\Nyos\Msg')) {
        \Nyos\Msg::sendTelegramm($for_msg, null, 1);

        if (isset($vv['admin_ajax_job'])) {
            foreach ($vv['admin_ajax_job'] as $k => $v) {
                \nyos\Msg::sendTelegramm($for_msg, $v);
//\Nyos\NyosMsg::sendTelegramm('Вход в управление ' . PHP_EOL . PHP_EOL . $e, $k );
            }
        }
    }

    \f\pa($log_errors, 2);
    file_put_contents($cash_file_errors, json_encode($log_errors));

    echo '<hr>';



//$r = '111';
// echo '<br/>'.__LINE__.'<div style="border: 2px solid orange; padding: 20px; max-height: 400px; overflow: auto;" >';
    \f\end2(( $r ?? 'x'), true);
// echo '</div>';
}






// считаем автооценку дня и пишем
elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'calc_full_ocenka_day') {

    try {

        // \f\pa($_REQUEST);

        $date = date('Y-m-d', strtotime($_REQUEST['date']));
        $sp = $_REQUEST['sp'];

        $return = array(
            'txt' => '',
            // текст о времени исполнения
            'time' => '',
            // смен в дне
            'smen_in_day' => 0,
            // часов за день отработано
            'hours' => 0,
            // больше или меньше нормы сделано сегодня ( 1 - больше или равно // 0 - меньше // 2 не получилось достать )
            'oborot_bolee_norm' => 2,
            // сумма денег на руки от количества смен и процента на ФОТ
            'summa_na_ruki' => 0,
            // рекомендуемая оценка управляющего
// если 0 то нет оценки
            'ocenka' => 0,
            'ocenka_naruki' => 0,
            'checks_for_new_ocenka' => [],
            'date' => $date,
            'sp' => $sp
        );


        // считаем сколько суммарно часов отработано за сегодня
        if (1 == 1) {

            \f\timer_start(2);
            // echo '<div style="border: 3px solid gray; padding: 20px; margin: 20px;" >hours<hr>';
            $hours = \Nyos\mod\JobDesc::calcJobHoursDay($db, $date, $sp);
            // echo '</div>';
            // \f\pa($hours,'','','hours');
            if (isset($hours['status']) && $hours['status'] == 'error') {
                throw new \Exception($hours['html'], 19);
            }

            // \f\pa($hours,'','','calc_hours');
//            if (!empty($hours['data']['hours']))
//                $return['hours'] = $hours['data']['hours'];

            foreach ($hours['data'] as $k => $v) {
                $return[$k] = $v;
            }

            //\f\pa($return);

            $return['time'] .= '<br/> посчитали сколько часов работы было в этот день'
                    . '<br/>' . \f\timer_stop(2);
        }


        /**
         * достаём нормы на день
         */
        if (5 == 5) {

            \f\timer_start(2);
            $now_norm = \Nyos\mod\JobDesc::whatNormToDay($db, $return['sp'], $return['date']);

            if ($now_norm === false)
                throw new \Exception('Нет плановых данных (дата)', 12);

            foreach ($now_norm as $k => $v) {
                $return['norm_' . $k] = $v;
            }

            $return['time'] .= '<br/> грузим нормы за день'
                    . '<br/>' . \f\timer_stop(2);

            if (empty($return['norm_date'])) {
                throw new \Exception('Нет плановых данных (дата)', 12);
            } elseif (empty($return['norm_vuruchka_on_1_hand']) || empty($return['norm_time_wait_norm_cold']) || empty($return['norm_procent_oplata_truda_on_oborota']) || empty($return['norm_kolvo_hour_in1smena'])) {
                throw new \Exception('Не все плановые данные по ТП указаны', 204);
            }
        }

        /**
         * достаём оборот за сегодня
         */
        if (5 == 5) {

            \f\timer_start(2);
            $return['oborot'] = \Nyos\mod\IikoOborot::getDayOborot($db, $return['sp'], $return['date']);

            if (empty($return['oborot'])) {
                $return['oborot'] = \Nyos\mod\IikoOborot::loadFromServerSaveItems($db, $return['sp'], $return['date']);
            }

            $return['time'] .= '<br/> достали обороты за день'
                    . '<br/>' . \f\timer_stop(2);
        }

        /**
         * достаём время ожидания за сегодня
         */
        if (5 == 5) {
            \f\timer_start(2);
            $timeo = \Nyos\mod\JobDesc::getTimeOgidanie($db, $return['sp'], $return['date']);
            $return['time'] .= '<br/>достали время ожидания за день'
                    . '<br/>' . \f\timer_stop(2);
            foreach ($timeo as $k => $v) {
                // $return['time'] .= PHP_EOL . $k . ' > ' . $v;
                $return[$k] = $v;
            }
        }

        $ocenka = \Nyos\mod\JobDesc::calcOcenkaDay($db, $return);
        \Nyos\mod\JobDesc::recordNewAutoOcenkiDay($db, $return['checks_for_new_ocenka'], $ocenka['data']['ocenka']);

        \Nyos\mod\items::deleteFromDops($db, \Nyos\mod\jobdesc::$mod_ocenki_days, [
            'sale_point' => $ocenka['data']['sp'],
            'date' => $ocenka['data']['date'],
        ]);

        \Nyos\mod\items::addNewSimple($db, \Nyos\mod\jobdesc::$mod_ocenki_days, [
            'sale_point' => $ocenka['data']['sp'],
            'date' => $ocenka['data']['date'],
            'ocenka_time' => $ocenka['data']['ocenka_time'],
            'ocenka_naruki' => $ocenka['data']['ocenka_naruki'],
            'ocenka' => $ocenka['data']['ocenka'],
                // 'txt' => ( $return['txt'] ?? '' ) . '<hr>' . ( $return['time'] ?? '' ),
        ]);

        \f\end2('ok ' . ( $ocenka['html'] ?? '--'), true, $ocenka);
    }
    //
    catch (Exception $ex) {

        \f\end2($ex->getMessage(), false, [
            'code' => $ex->getCode(),
            'file' => $ex->getFile(),
            'line' => $ex->getLine(),
            'trace' => $ex->getTraceAsString()]
        );
    }


    if (1 == 2) {
        if (1 == 1) {

            \f\pa($return, '', '', '$return');

//$job_now_on_sp    
            // echo '<br/>'.__FILE__.' #'.__LINE__;
            echo '<fieldset>';
            $worker_on_date = \Nyos\mod\JobDesc::whereJobmans($db, $date);
            echo '</fieldset>';
            //\f\pa($worker_on_date, 2, '', 'self::whereJobmans($db, $date);');

            die('<br/>end ' . __FILE__ . ' #' . __LINE__);

// id items для записи авто оценки

            /**
             * достаём чеки за день
             */
            if (1 == 1) {

//        echo '<fieldset><legend>\Nyos\mod\JobDesc::getTimesChecksDay '.__FILE__.' #'.__LINE__.'</legend>';
//
//        $id_items_for_new_ocenka = [];
//        \f\timer::start();
//
//        // $return['hours'] = \Nyos\mod\JobDesc::getTimesChecksDay($db, $sp, $e) getOborotSp($db, $return['sp'], $return['date']);

                $times_day = \Nyos\mod\JobDesc::getTimesChecksDay($db, $return['sp'], $return['date']);
                \f\pa($times_day, 2, '', '\Nyos\mod\JobDesc::getTimesChecksDay');
//
//        $return['hours'] = $times_day['hours'];
//        $id_items_for_new_ocenka = $times_day['id_check_for_new_ocenka'];
//        // die($return['hours']);
//
//        $return['time'] .= PHP_EOL . ' достали время работы по чекам за день : ' . \f\timer::stop()
//            . PHP_EOL . $return['hours'];
//
//        echo '</fieldset>';
            }

            die('<br/>end ' . __FILE__ . ' #' . __LINE__);

//        if (!class_exists('Nyos\mod\JobDesc'))
//            require_once DR . DS . 'vendor/didrive_mod/jobdesc/class.php';
//        echo '<br/>' . __FILE__ . ' ' . __LINE__;
//        \f\pa($return);
//        die(__LINE__);
//        echo '<fieldset style="border: 1px solid gray; padding: 5px; margin: 5px;" ><legend>'
//        . 'достаём суммарное время работы сотрудников за сегодня</legend>';
            if (1 == 3) {

//            echo '<hr>';
//            echo __FILE__.' #'.__LINE__;
//            echo '<hr>';
//            echo '<hr>';
// $sp

                $worker_on_date = self::whereJobmansNowDate($db, $return['date']);
// \f\pa($worker_on_date, 2, '', '$worker_on_date');

                $ds = strtotime($return['date'] . ' 09:00:00');
                $df = strtotime($return['date'] . ' 03:00:00 +1 day');

                $ds1 = date('Y-m-d H:i:s', $ds);
// echo '<Br/>'.date('Y-m-d H:i:s', $ds );
                $df1 = date('Y-m-d H:i:s', $df);
// echo '<Br/>'.date('Y-m-d H:i:s', $df );

                $checks = \Nyos\mod\items::getItemsSimple($db, self::$mod_checks);
                $return['checks_for_new_ocenka'] = [];

                foreach ($checks['data'] as $k3 => $v3) {

                    if (
                            isset($v3['dop']['jobman']) &&
                            isset($v3['dop']['start']) &&
                            $v3['dop']['start'] >= $ds1 &&
                            $v3['dop']['start'] <= $df1 &&
                            isset($worker_on_date[$v3['dop']['jobman']]['sale_point']) &&
                            $worker_on_date[$v3['dop']['jobman']]['sale_point'] == $sp
                    ) {

//\f\pa($v3['dop']);
//break;
// часы отредактированные в ручную
                        if (!empty($v3['dop']['hour_on_job_hand'])) {
                            $return['checks_for_new_ocenka'][] = $v3['id'];
                            $return['hours'] += $v3['dop']['hour_on_job_hand'];
                        }
// авторасчёт количества часов
                        elseif (!empty($v3['dop']['hour_on_job'])) {
                            $return['checks_for_new_ocenka'][] = $v3['id'];
                            $return['hours'] += $v3['dop']['hour_on_job'];
                        }
                    }
                }
//die();
//            $return['smen_in_day'] = round($return['hours'] / $return['norm_kolvo_hour_in1smena'], 1);
//            
//            if( !empty($return['oborot']) && !empty($return['smen_in_day']) )
//            $return['summa_na_ruki'] = ceil( $return['oborot'] / $return['smen_in_day'] );
//
//            // если на руки больше нормы то оценка 5
//            if ( $return['summa_na_ruki'] >= $return['norm_vuruchka_on_1_hand']) {
//                $return['ocenka_naruki'] = 5;
//            }
//            // если на руки меньше нормы то оценка 3
//            else {
//                $return['ocenka_naruki'] = 3;
//            }
// $ee = self::getTimesChecksDay($db, $return['sp'], $return['date']);
// \f\pa($ee, 2, '', '$ee = self::getTimesChecksDay($db, $ar[\'sp\'], $ar[\'date\']);');
// $return['hours_job_days'] = $ee;
            }
// echo '</fieldset>';
//        return \f\end3('ok', true, $return);

            die('<br/>end ' . __FILE__ . ' #' . __LINE__);





            $e = \Nyos\mod\jobdesc::calculateAutoOcenkaDays($db, $_REQUEST['sp'], $_REQUEST['date']);

            // \f\pa($e, 2, '', '$ee1 результ оценки дня 1 (функция) action=calc_full_ocenka_day');

            if (!empty($e['data']['error'])) {
                \f\end2($e['data']['error'], false, $e);
            } else {
                \f\end2('ok', true, $e);
            }

            die('<br/>end ' . __FILE__ . ' #' . __LINE__);
        }



        if (1 == 2) {

            echo __FUNCTION__ . ' ' . __FILE__ . ' ' . __LINE__ . '<hr>';

//    
//    $r = \Nyos\mod\JobDesc::getTimesChecksDay($db, $_REQUEST['sp'], $_REQUEST['date']);
//    \f\pa($r,'','','\Nyos\mod\JobDesc::getTimesChecksDay');
//    
            /**
             * перенёс в отдельную функцию 
             * \Nyos\mod\jobdesc\calculateAutoOcenkaDays($db, $sp, $data)
             */
            $ee1 = \Nyos\mod\jobdesc::calculateAutoOcenkaDays($db, $_REQUEST['sp'], $_REQUEST['date']);

// \f\pa($ee1, 2, '', '$ee1 результ оценки дня 1 (функция)');
            if (!empty($ee1['data']['error'])) {
                \f\end2($ee1['data']['error'], false, $ee1);
            } else {
                \f\end2('ok', true, $ee1);
            }
        }

// ob_start('ob_gzhandler');

        try {

            if (1 == 1) {
                $return = \Nyos\mod\JobDesc::readVarsForOcenkaDays($db, $_REQUEST['sp'], $_REQUEST['date']);
// \f\pa($return, 2, '', '$return данные для оценки дня');
// массив чеков для новых оценок
// $return['checks_for_new_ocenka']
            }

            if (1 == 1) {
// \f\pa($return['data'], 2, '', '$return данные для оценки дня');
                $ocenka = \Nyos\mod\JobDesc::calcOcenkaDay($db, $return['data']);
// \f\pa($ocenka, 2, '', '$ocenka');
            }

//        if ( class_exists('\Nyos\mod\items') )
//            echo '<br/>' . __FILE__ . ' ' . __LINE__;
// if (!empty($return['data']['checks_for_new_ocenka'])) {
// \f\pa( $return['checks_for_new_ocenka'], 2 , '' , 'checks_for_new_ocenka' );
// }

            \Nyos\mod\JobDesc::recordNewAutoOcenkiDay($db, $return['data']['checks_for_new_ocenka'], $ocenka['data']['ocenka']);

            \Nyos\mod\items::addNewSimple($db, \Nyos\mod\jobdesc:: $mod_ocenki_days, [
                'sale_point' => $ocenka['data']['sp'],
                'date' => $ocenka['data']['date'],
                'ocenka_time' => $ocenka['data']['ocenka_time'],
                'ocenka_naruki' => $ocenka['data']['ocenka_naruki'],
                'ocenka' => $ocenka['data']['ocenka'],
            ]);

//        $r = ob_get_contents();
//        ob_end_clean();

            \f\end2('ok ' . ( $r ?? '--'), true, $return['data']);

            if (1 == 2) {

// require_once DR . '/all/ajax.start.php';
// $ff = $db->prepare('UPDATE `mitems` SET `status` = \'hide\' WHERE `id` = :id ');
// $ff->execute(array(':id' => (int) $_POST['id2']));
//die('123');
//
//echo '<br/>'.__FILE__.' '.__LINE__;
//    $checki = \Nyos\mod\items::getItemsSimple($db, '050.chekin_checkout', 'show');
//    \f\pa($checki,2,'','$checki');
//echo '<br/>'.__FILE__.' '.__LINE__;
//    $salary = \Nyos\mod\JobDesc::configGetJobmansSmenas($db);
//    \f\pa($salary,2,'','$salary');
//    $return['txt'] .= '<br/>salary';
//    foreach ($salary as $k => $v) {
//        $return['txt'] .= '<br/><nobr>[' . $k . '] - ' . $v . '</nobr>';
//        $return['salary_' . $k] = $v;
//    }
//echo '<br/>'.__FILE__.' '.__LINE__;
//echo '<br/>'.__FILE__.' '.__LINE__;
//echo '<br/>'.__FILE__.' '.__LINE__;
// \f\pa($return);
// exit;
//\f\pa($return);
// если есть ошибки
                if (!empty($error)) {

                    require_once DR . dir_site . 'config.php';

                    $sp = \Nyos\mod\items::getItemsSimple($db, 'sale_point', 'show');
// \f\pa($sp);

                    if (!isset($_REQUEST['no_send_msg'])) {
                        $txt_to_tele = 'Обнаружены ошибки при расчёте оценки точки продаж (' . $sp['data'][$_REQUEST['sp']]['head'] . ') за день работы (' . $_REQUEST['date'] . ')' . PHP_EOL . PHP_EOL . $error;

                        if (class_exists('\nyos\Msg'))
                            \nyos\Msg::sendTelegramm($txt_to_tele, null, 1);

                        if (isset($vv['admin_ajax_job'])) {
                            foreach ($vv['admin_ajax_job'] as $k => $v) {
                                \nyos\Msg::sendTelegramm($txt_to_tele, $v);
//\Nyos\NyosMsg::sendTelegramm('Вход в управление ' . PHP_EOL . PHP_EOL . $e, $k );
                            }
                        }
                    }
//echo '<br/>'.__FILE__.' '.__LINE__;

                    return \f\end2('Обнаружены ошибки при расчёте оценки точки продаж (' . $_REQUEST['sp'] . ') за день работы (' . $_REQUEST['date'] . ')' . $error, false);
                }
// если нет ошибок считаем
                else {

                    \f\timer::start();

                    /**
                     * сравниваем время ожидания холодный цех
                     */
                    if (isset($return['timeo_cold']) && isset($return['norm_time_wait_norm_cold'])) {

                        $return['txt'] .= '<br/><br/>-------------------';
                        $return['txt'] .= '<br/>время ожидания (хол.цех)';
                        $return['txt'] .= '<br/>по плану: ' . $return['norm_time_wait_norm_cold'] . ' и значение в ТП ' . $return['timeo_cold'];

                        if (isset($return['timeo_cold']) && isset($return['norm_time_wait_norm_cold']) &&
                                $return['timeo_cold'] > $return['norm_time_wait_norm_cold']) {

                            $return['txt'] .= '<br/>не норм, оценка 3';
                            $return['ocenka_time'] = 3;
                            $return['ocenka'] = 3;
                        } else {
                            $return['txt'] .= '<br/>норм, оценка 5';
                            $return['ocenka_time'] = 5;
                        }
                    } else {
                        throw new \Exception('Вычисляем оценку дня, прервано, не хватает данных по времени ожидания', 14);
                    }

                    /**
                     * сравниваем объём выручки
                     */
                    if (1 == 2) {
                        if (!empty($return['norm_vuruchka']) && !empty($return['oborot'])) {

                            $return['txt'] .= '<br/><br/>-------------------';
                            $return['txt'] .= '<br/>норма выручки';
                            $return['txt'] .= '<br/>по плану: ' . $return['norm_vuruchka'] . ' и значение в ТП ' . $return['oborot'];

                            if ($return['oborot'] >= $return['norm_vuruchka']) {
                                $return['oborot_bolee_norm'] = 1;
                                $return['ocenka_oborot'] = 5;
                                $return['txt'] .= '<br/>норм, оценка 5';
                            } else {
                                $return['oborot_bolee_norm'] = 0;
                                $return['ocenka_oborot'] = 3;
                                $return['ocenka'] = 3;
                                $return['txt'] .= '<br/>не норм, оценка 3';
                            }
                        }
//
                        else {
                            throw new \Exception('Вычисляем оценку дня, прервано, не хватает данных по обороту за сутки', 18);
                        }
                    }

                    /**
                     * считаем норму выручки на руки
                     */
// if (!empty($return['norm_kolvo_hour_in1smena'])) {
                    if (!empty($return['norm_kolvo_hour_in1smena']) && !empty($return['norm_vuruchka_on_1_hand'])) {

                        $return['txt'] .= '<br/><br/>-------------------';
                        $return['txt'] .= '<br/>норма выручки (на руки)';

                        $return['smen_in_day'] = round($return['hours'] / $return['norm_kolvo_hour_in1smena'], 1);
                        $return['txt'] .= '<br/>Кол-во поваров: ' . $return['smen_in_day'];

                        $return['on_hand_fakt'] = ceil($return['oborot'] / $return['smen_in_day']);
// $return['summa_na_ruki_norm'] = ceil($return['oborot'] / 100 * $return['norm_procent_oplata_truda_on_oborota']);
//$return['txt'] .= '<br/>по плану: ' . $return['summa_na_ruki_norm'] . ' и значение в ТП ' . $return['on_hand_fakt'];
                        $return['txt'] .= '<br/>по плану: ' . $return['norm_vuruchka_on_1_hand'] . ' и значение в ТП ' . $return['on_hand_fakt'];

                        if ($return['on_hand_fakt'] < $return['norm_vuruchka_on_1_hand']) {
                            $return['ocenka'] = 3;
                            $return['ocenka_naruki'] = 3;
                            $return['ocenka'] = 3;
                            $return['txt'] .= '<br/>не норм, оценка 3';
                        } else {
                            $return['ocenka_naruki'] = 5;
                            $return['txt'] .= '<br/>норм, оценка 5';
                        }
                    } else {
                        throw new \Exception('Вычисляем оценку дня, прервано, не хватает значения по плану (норма на руки)', 19);
                    }


                    $return['txt'] .= '<br/>';
                    $return['txt'] .= '<br/>';
                    $return['txt'] .= '-----------';
                    $return['txt'] .= '<br/>';
                    $return['txt'] .= 'оценка дня : ' . $return['ocenka'];
                    $return['txt'] .= '<br/>';
                    $return['txt'] .= '<br/>';
                    $return['txt'] .= '<br/>';

// $return['ocenka_upr'] = $return['ocenka'];
//            $return['time'] .= PHP_EOL . ' считаем ходится не сходится : ' . \f\timer::stop();
//            $return['txt'] .= '<br/><nobr>рекомендуемая оценка упр: ' . $return['ocenka_upr'] . '</nobr>';


                    /**
                     * запись результатов в бд
                     */
                    if (1 == 1) {
                        $sql_del = '';
                        $sql_ar_new = [];

                        foreach ($id_items_for_new_ocenka as $id_item => $v) {

                            $sql_del .= (!empty($sql_del) ? ' OR ' : '' ) . ' id_item = \'' . (int) $id_item . '\' ';
                            $sql_ar_new[] = array(
                                'id_item' => $id_item,
                                'name' => 'ocenka_auto',
                                'value' => $return['ocenka']
                            );
                        }

                        if (!empty($sql_del)) {
                            $ff = $db->prepare('DELETE FROM `mitems-dops` WHERE name = \'ocenka_auto\' AND ( ' . $sql_del . ' ) ');
                            $ff->execute();
                        }

                        \f\db\sql_insert_mnogo($db, 'mitems-dops', $sql_ar_new);
                        $return['txt'] .= '<br/>записали автоценки сотрудникам';
                    }

                    require_once DR . dir_site . 'config.php';

                    $sp = \Nyos\mod\items::getItemsSimple($db, 'sale_point', 'show');
// \f\pa($sp);

                    \Nyos\mod\items::addNewSimple($db, 'sp_ocenki_job_day', $return);

                    if (!isset($_REQUEST['no_send_msg']) && !isset($_REQUEST['telega_no_send'])) {

                        $txt_to_tele = 'Расчитали автооценку ( ' . $sp['data'][$_REQUEST['sp']]['head'] . ' ) за день работы (' . $_REQUEST['date'] . ')'
                                . PHP_EOL
                                . PHP_EOL
                                . str_replace('<br/>', PHP_EOL, $return['txt'])
//                        . PHP_EOL
//                        . '-----------------'
//                        . PHP_EOL
//                        . 'время выполнения вычислений'
//                        . PHP_EOL
//                        . $return['time']
                        ;

                        if (class_exists('\nyos\Msg'))
                            \nyos\Msg::sendTelegramm($txt_to_tele, null, 1);

                        if (isset($vv['admin_ajax_job'])) {
                            foreach ($vv['admin_ajax_job'] as $k => $v) {
                                \nyos\Msg::sendTelegramm($txt_to_tele, $v);
//\Nyos\NyosMsg::sendTelegramm('Вход в управление ' . PHP_EOL . PHP_EOL . $e, $k );
                            }
                        }
                    }

                    \f\end2(
                            $return['txt']
                            . '<br/>часов: ' . $return['hours']
                            . '<br/>смен в дне: ' . $return['smen_in_day']
                            , true, $return);
                }

//return \f\end2('Обнаружены ошибки: ' . $ex->getMessage() . ' <Br/>' . $text, false, array( 'error' => $ex->getMessage() ) );        
            }
        }
//
        catch (\Exception $ex) {

// if ( isset($_REQUEST['no_send_msg']) ) {}else{}

            $text = $ex->getMessage()
                    . ' авторасчёт оценки дня'
                    . PHP_EOL
                    . PHP_EOL
                    . ' sp:' . ( $return['data']['sp'] ?? '--' )
                    . ' date:' . ( $return['data']['date'] ?? '--' )
                    . PHP_EOL
                    . PHP_EOL
                    . '--- ' . __FILE__ . ' ' . __LINE__ . '-------'
                    . PHP_EOL
                    . $ex->getMessage() . ' #' . $ex->getCode()
                    . PHP_EOL
                    . $ex->getFile() . ' #' . $ex->getLine()
                    . PHP_EOL
                    . $ex->getTraceAsString()
// . '</pre>'
            ;

            if (1 == 2) {
                if (class_exists('\Nyos\Msg'))
                    \Nyos\Msg::sendTelegramm($text, null, 1);
            }

            /*
              echo '<pre>'
              . PHP_EOL
              . PHP_EOL
              . '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
              . PHP_EOL
              . $ex->getMessage() . ' #' . $ex->getCode()
              . PHP_EOL
              . $ex->getFile() . ' #' . $ex->getLine()
              . PHP_EOL
              . $ex->getTraceAsString()
              . '</pre>';
             */

            /*

              require_once DR . dir_site . 'config.php';

              $sp = \Nyos\mod\items::getItemsSimple($db, 'sale_point', 'show');
              // \f\pa($sp);

              $txt_to_tele = 'Обнаружены ошибки при расчёте оценки точки продаж (' . $sp['data'][$_REQUEST['sp']]['head'] . ') за день работы (' . $_REQUEST['date'] . ')' . PHP_EOL . PHP_EOL . $error;

              if (class_exists('\nyos\Msg'))
              \nyos\Msg::sendTelegramm($txt_to_tele, null, 1);

              if (isset($vv['admin_ajax_job'])) {
              foreach ($vv['admin_ajax_job'] as $k => $v) {
              \nyos\Msg::sendTelegramm($txt_to_tele, $v);
              //\Nyos\NyosMsg::sendTelegramm('Вход в управление ' . PHP_EOL . PHP_EOL . $e, $k );
              }
              }
             */

            $r = ob_get_contents();
            ob_end_clean();

            \f\end2('Обнаружены ошибки: ' . $ex->getMessage(), false, [
                'error' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'sp' => ( $return['data']['sp'] ?? null),
                'date' => ( $return['data']['date'] ?? null),
                'text' => $text . '<br/>' . $r,
            ]);
        }
    }
}

// удаление смены персонала
elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'cancel_smena') {

//echo '<br/>'. __FILE__.' '.__LINE__;

    try {

// \f\pa($_REQUEST);

        $ff = $db->prepare('UPDATE `mitems` SET `status` = \'delete\' WHERE `id` = :id ');
        $ff->execute(array(':id' => $_REQUEST['id']));

        \f\end2('назначение отменено', true);
    }
//
    catch (\Exception $ex) {

        if (!isset($_REQUEST['no_send_msg'])) {

            $text = $ex->getMessage()
                    . PHP_EOL
                    . PHP_EOL
                    . '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
                    . PHP_EOL
                    . $ex->getMessage() . ' #' . $ex->getCode()
                    . PHP_EOL
                    . $ex->getFile() . ' #' . $ex->getLine()
                    . PHP_EOL
                    . $ex->getTraceAsString()
                    . '</pre>';

            if (class_exists('\nyos\Msg'))
                \nyos\Msg::sendTelegramm($text, null);
        }
        /*

          require_once DR . dir_site . 'config.php';

          $sp = \Nyos\mod\items::getItemsSimple($db, 'sale_point', 'show');
          // \f\pa($sp);

          $txt_to_tele = 'Обнаружены ошибки при расчёте оценки точки продаж (' . $sp['data'][$_REQUEST['sp']]['head'] . ') за день работы (' . $_REQUEST['date'] . ')' . PHP_EOL . PHP_EOL . $error;

          if (class_exists('\nyos\Msg'))
          \nyos\Msg::sendTelegramm($txt_to_tele, null, 1);

          if (isset($vv['admin_ajax_job'])) {
          foreach ($vv['admin_ajax_job'] as $k => $v) {
          \nyos\Msg::sendTelegramm($txt_to_tele, $v);
          //\Nyos\NyosMsg::sendTelegramm('Вход в управление ' . PHP_EOL . PHP_EOL . $e, $k );
          }
          }
         */
        return \f\end2('Обнаружены ошибки: ' . $ex->getMessage() . ' <Br/>' . $text, false, array('error' => $ex->getMessage(), 'code' => $ex->getCode()));
    }
}

// удаление назначения сотрудника
elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_workman_from_sp') {

//echo '<br/>'. __FILE__.' '.__LINE__;

    try {
// \f\pa($_REQUEST);

        \Nyos\mod\items::deleteItemsSimple($db, 'jobman_send_on_sp', array(
            'jobman' => $_REQUEST['workman'],
            'sale_point' => $_REQUEST['sp']
        ));

        \f\end2('ок', true);
    }
//
    catch (\Exception $ex) {

        if (!isset($_REQUEST['no_send_msg'])) {

            $text = $ex->getMessage()
                    . PHP_EOL
                    . PHP_EOL
                    . '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
                    . PHP_EOL
                    . $ex->getMessage() . ' #' . $ex->getCode()
                    . PHP_EOL
                    . $ex->getFile() . ' #' . $ex->getLine()
                    . PHP_EOL
                    . $ex->getTraceAsString()
                    . '</pre>';

            if (class_exists('\nyos\Msg'))
                \nyos\Msg::sendTelegramm($text, null);
        }

        return \f\end2('Обнаружены ошибки: ' . $ex->getMessage() . ' <Br/>' . $text, false, array('error' => $ex->getMessage(), 'code' => $ex->getCode()));
    }
}




// обозначаем конец текущего рабочего периода
elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'set_end_now_jobs') {

//echo '<br/>'. __FILE__.' '.__LINE__;

    try {

        if (empty($_REQUEST['date_end']))
            throw new \Exception('не указана дата');

        $ff = $db->prepare('DELETE FROM `mitems-dops` WHERE `id_item` = :id AND name = \'date_finish\' ');
        $ff->execute(array(':id' => (int) $_REQUEST['work_id']));

        \f\db\db2_insert(
                $db, 'mitems-dops', array(
            'id_item' => (int) $_REQUEST['work_id'],
            'name' => 'date_finish',
            'value_date' => date('Y-m-d', strtotime($_REQUEST['date_end']))
                )
        );

// \f\pa($_REQUEST);
//        \Nyos\mod\items::deleteItemsSimple($db, 'jobman_send_on_sp', array(
//            'jobman' => $_REQUEST['workman'],
//            'sale_point' => $_REQUEST['sp']
//        ));

        \f\end2('ок', true);
    }
//
    catch (\Exception $ex) {

        if (!isset($_REQUEST['no_send_msg'])) {

            $text = $ex->getMessage()
                    . PHP_EOL
                    . PHP_EOL
                    . '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
                    . PHP_EOL
                    . $ex->getMessage() . ' #' . $ex->getCode()
                    . PHP_EOL
                    . $ex->getFile() . ' #' . $ex->getLine()
                    . PHP_EOL
                    . $ex->getTraceAsString()
                    . '</pre>';

            if (class_exists('\nyos\Msg'))
                \nyos\Msg::sendTelegramm($text, null);
        }

        return \f\end2('Обнаружены ошибки: ' . $ex->getMessage() . ' <Br/>' . $text, false, array('error' => $ex->getMessage(), 'code' => $ex->getCode()));
    }
}

// обозначаем конец текущего рабочего периода
elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'cancel_end_now_jobs') {

//echo '<br/>'. __FILE__.' '.__LINE__;

    try {

        $ff = $db->prepare('DELETE FROM `mitems-dops` WHERE `id_item` = :id AND name = \'date_finish\' ');
        $ff->execute(array(':id' => (int) $_REQUEST['work_id']));

// \f\pa($_REQUEST);
//        \Nyos\mod\items::deleteItemsSimple($db, 'jobman_send_on_sp', array(
//            'jobman' => $_REQUEST['workman'],
//            'sale_point' => $_REQUEST['sp']
//        ));

        \f\end2('ок', true);
    }
//
    catch (\Exception $ex) {

        if (!isset($_REQUEST['no_send_msg'])) {

            $text = $ex->getMessage()
                    . PHP_EOL
                    . PHP_EOL
                    . '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
                    . PHP_EOL
                    . $ex->getMessage() . ' #' . $ex->getCode()
                    . PHP_EOL
                    . $ex->getFile() . ' #' . $ex->getLine()
                    . PHP_EOL
                    . $ex->getTraceAsString()
                    . '</pre>';

            if (class_exists('\nyos\Msg'))
                \nyos\Msg::sendTelegramm($text, null);
        }

        return \f\end2('Обнаружены ошибки: ' . $ex->getMessage() . ' <Br/>' . $text, false, array('error' => $ex->getMessage(), 'code' => $ex->getCode()));
    }
}

// action=put_workman_on_sp
elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'put_workman_on_sp') {

//echo '<br/>'. __FILE__.' '.__LINE__;
    try {

        if (isset($_REQUEST['sp']) && isset($_REQUEST['sp_s']) && \Nyos\Nyos::checkSecret($_REQUEST['sp_s'], $_REQUEST['sp']) === true) {
            
        } else {
            throw new \Exception('Произошла неописуемая ситуация #' . __LINE__, 107);
        }

        if (
                empty($_REQUEST['dolgn']) ||
                empty($_REQUEST['date']) ||
                empty($_REQUEST['sp']) ||
                empty($_REQUEST['user'])) {
            throw new \Exception('Произошла неописуемая ситуация #' . __LINE__, 108);
        }

        $d = [];
        $d['jobman'] = $_REQUEST['user'];
        $d['sale_point'] = $_REQUEST['sp'];
        $d['dolgnost'] = $_REQUEST['dolgn'];
        $d['date'] = date('Y-m-d', strtotime($_REQUEST['date']));

        if (!empty($_REQUEST['smoke']))
            $d['smoke'] = 'da';

        if (isset($_REQUEST['type2']) && $_REQUEST['type2'] == 'spec_naznach') {

            if (!empty($_REQUEST['sp_from']))
                $d['sale_point_from'] = $_REQUEST['sp_from'];

            if (!empty($_REQUEST['dolgnost_from']))
                $d['dolgnost_from'] = $_REQUEST['dolgnost_from'];

            \Nyos\mod\items::addNewSimple($db, '050.job_in_sp', $d);
        } else {
            \Nyos\mod\items::addNewSimple($db, 'jobman_send_on_sp', $d);
        }

        \f\end2('добавили', true);
//return \f\end2('Обнаружены ошибки: ' . $ex->getMessage() . ' <Br/>' . $text, false, array( 'error' => $ex->getMessage() ) );        
    }
//
    catch (\Exception $ex) {

        if (!isset($_REQUEST['no_send_msg'])) {

            $text = $ex->getMessage()
                    . PHP_EOL
                    . PHP_EOL
                    . '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
                    . PHP_EOL
                    . $ex->getMessage() . ' #' . $ex->getCode()
                    . PHP_EOL
                    . $ex->getFile() . ' #' . $ex->getLine()
                    . PHP_EOL
                    . $ex->getTraceAsString()
                    . '</pre>';

            if (class_exists('\nyos\Msg'))
                \nyos\Msg::sendTelegramm($text, null);
        }
        /*

          require_once DR . dir_site . 'config.php';

          $sp = \Nyos\mod\items::getItemsSimple($db, 'sale_point', 'show');
          // \f\pa($sp);

          $txt_to_tele = 'Обнаружены ошибки при расчёте оценки точки продаж (' . $sp['data'][$_REQUEST['sp']]['head'] . ') за день работы (' . $_REQUEST['date'] . ')' . PHP_EOL . PHP_EOL . $error;

          if (class_exists('\nyos\Msg'))
          \nyos\Msg::sendTelegramm($txt_to_tele, null, 1);

          if (isset($vv['admin_ajax_job'])) {
          foreach ($vv['admin_ajax_job'] as $k => $v) {
          \nyos\Msg::sendTelegramm($txt_to_tele, $v);
          //\Nyos\NyosMsg::sendTelegramm('Вход в управление ' . PHP_EOL . PHP_EOL . $e, $k );
          }
          }
         */
        return \f\end2('Обнаружены ошибки: ' . $ex->getMessage() . ' <Br/>' . $text, false, array('error' => $ex->getMessage(), 'code' => $ex->getCode()));
    }
}

//
elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_ocenka') {

    if (empty($_REQUEST['sp']) || empty($_REQUEST['date']) || empty($_REQUEST['s']))
        \f\end2('не хватает данных', false);

    if (\Nyos\Nyos::checkSecret($_REQUEST['s'], $_REQUEST['sp'] . $_REQUEST['date']) === false)
        \f\end2('в данных какая то ошибка', false);

    \Nyos\mod\items::deleteFromDops($db, \Nyos\mod\JobDesc::$mod_ocenki_days, ['date' => $_REQUEST['date'], 'sale_point' => $_REQUEST['sp']]);

//    \Nyos\mod\items::$join_where = ' INNER JOIN `mitems-dops` md1 ON md1.id_item = mi.id AND md1.name = \'date\' AND md1.value_date = :date
//            INNER JOIN `mitems-dops` md2 ON md2.id_item = mi.id AND md2.name = \'sale_point\' AND md2.value = :sp ';
//
//    \Nyos\mod\items::$var_ar_for_1sql = [
//        ':sp' => $_REQUEST['sp'],
//        ':date' => date('Y-m-d', strtotime($_REQUEST['date']))
//    ];
//
//    \Nyos\mod\items::$limit1 = true;
//    $n = \Nyos\mod\items::get($db, \Nyos\mod\JobDesc::$mod_ocenki_days);
//    // \f\pa($n);
//    
//    $e = \Nyos\mod\items::deleteId($db, $n['id'] );
//// require_once DR . '/all/ajax.start.php';
//
//    $ff = $db->prepare('UPDATE `mitems` SET `status` = \'delete\' WHERE `id` = :id ');
//    $ff->execute(array(':id' => (int) $n['id']));

    \f\end2('удалено', true);
}

//
elseif (isset($_POST['action']) && ( $_POST['action'] == 'delete_smena' || $_POST['action'] == 'delete_comment')) {

// require_once DR . '/all/ajax.start.php';

    $ff = $db->prepare('UPDATE `mitems` SET `status` = \'hide\' WHERE `id` = :id ');
    $ff->execute(array(':id' => (int) $_POST['id2']));

    \f\end2('удалено');
}
//
elseif (isset($_POST['action']) && $_POST['action'] == 'recover_smena') {

    require_once DR . '/all/ajax.start.php';

    $ff = $db->prepare('UPDATE `mitems` SET `status` = \'show\' WHERE `id` = :id ');
    $ff->execute(array(':id' => (int) $_POST['id2']));

    \f\end2('смена восстановлена');
}
//
elseif (
        isset($_POST['action']) && (
        $_POST['action'] == 'add_new_smena' ||
        $_POST['action'] == 'add_comment' ||
        $_POST['action'] == 'confirm_smena' ||
        $_POST['action'] == 'goto_other_sp'
        )
) {
// action=add_new_smena

    try {

//require_once DR . '/all/ajax.start.php';
// action=add_new_smena
// \f\pa($_POST);
// [date] => 2019-06-27
// [toform_sp] => 2611
// [action] => goto_other_sp
// [id2] => 10    
// [jobman] => 1886        
        /**
         * отправляем сотрудника на другую точку
         */
        if ($_POST['action'] == 'goto_other_sp') {

//            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'))
//                require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
//
//            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/class.php'))
//                require ($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/class.php');
// если старт часов меньше часов сдачи
            if (strtotime($_REQUEST['start_time']) > strtotime($_REQUEST['fin_time'])) {
//$b .= '<br/>'.__LINE__;
                $start_time = strtotime($_REQUEST['date'] . ' ' . $_REQUEST['start_time']);
                $fin_time = strtotime($_REQUEST['date'] . ' ' . $_REQUEST['fin_time']) + 3600 * 24;
            }
// если старт часов больше часов сдачи
            else {
//$b .= '<br/>'.__LINE__;
                $start_time = strtotime($_REQUEST['date'] . ' ' . $_REQUEST['start_time']);
                $fin_time = strtotime($_REQUEST['date'] . ' ' . $_REQUEST['fin_time']);
            }

            \Nyos\mod\items::addNew($db, $vv['folder'], \Nyos\nyos::$menu['050.chekin_checkout'], array(
                'head' => rand(100, 100000),
                'jobman' => $_REQUEST['jobman'],
                'sale_point' => $_REQUEST['salepoint'],
                'start' => date('Y-m-d H:i', $start_time),
                'fin' => date('Y-m-d H:i', $fin_time)
            ));

            \f\end2('<div>'
                    . '<nobr><b class="warn" >смена добавлена</b>'
                    . '<br/>'
                    . date('d.m.y H:i', $start_time) . ' - ' . date('d.m.y H:i', $fin_time)
                    . '</nobr>'
                    . '</div>', true);
        }
//
        elseif ($_POST['action'] == 'add_new_smena') {

//            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'))
//                require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
//
//            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/class.php'))
//                require ($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/class.php');
// если старт часов меньше часов сдачи
            if (strtotime($_REQUEST['start_time']) > strtotime($_REQUEST['fin_time'])) {
//$b .= '<br/>'.__LINE__;
                $start_time = strtotime($_REQUEST['date'] . ' ' . $_REQUEST['start_time']);
                $fin_time = strtotime($_REQUEST['date'] . ' ' . $_REQUEST['fin_time']) + 3600 * 24;
            }
// если старт часов больше часов сдачи
            else {
//$b .= '<br/>'.__LINE__;
                $start_time = strtotime($_REQUEST['date'] . ' ' . $_REQUEST['start_time']);
                $fin_time = strtotime($_REQUEST['date'] . ' ' . $_REQUEST['fin_time']);
            }

            $indb = array(
                'head' => rand(100, 100000),
                'jobman' => $_REQUEST['jobman'],
                'sale_point' => $_REQUEST['salepoint'],
                'start' => date('Y-m-d H:i', $start_time),
                'fin' => date('Y-m-d H:i', $fin_time),
                'hour_on_job' => \Nyos\mod\IikoChecks::calculateHoursInRange(date('Y-m-d H:i', $start_time), date('Y-m-d H:i', $fin_time)),
                // 'hour_on_job' => \Nyos\mod\IikoChecks::calculateHoursInRangeUnix($start_time, $fin_time),
                'who_add_item' => 'admin',
                'who_add_item_id' => $_SESSION['now_user_di']['id'] ?? '',
                'ocenka' => $_REQUEST['ocenka']
            );

            //\f\pa($indb);

            \Nyos\mod\items::addNew($db, $vv['folder'], \Nyos\nyos::$menu['050.chekin_checkout'], $indb);

            \f\end2('<div>'
                    . '<nobr><b class="warn" >смена добавлена</b>'
                    . '<br/>'
                    . date('d.m.y H:i', $start_time) . ' - ' . date('d.m.y H:i', $fin_time)
                    . '</nobr>'
                    . '</div>', true);
        } elseif ($_POST['action'] == 'add_comment') {

            $indb = $_REQUEST;

//array(
//                // 'head' => rand(100, 100000),
//                'jobman' => $_REQUEST['jobman'],
//                'sale_point' => $_REQUEST['salepoint'],
//                'start' => date('Y-m-d H:i', $start_time),
//                'fin' => date('Y-m-d H:i', $fin_time)
//            )
//\f\pa( $indb );
            \Nyos\mod\items::addNew($db, $vv['folder'], \Nyos\nyos::$menu['073.comments'], $indb);

            \f\end2('<div style="background-color: gray; padding:5px;" >'
                    . '<b class="warn" >добавили комментарий</b>'
                    . '<br/>'
                    . $_REQUEST['comment']
                    . '</div>', true);
        }
//
        elseif ($_POST['action'] == 'confirm_smena') {

//        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'))
//            require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

            $ff = $db->prepare('DELETE FROM `mitems-dops` WHERE `id_item` = :id AND `name` = \'pay_check\' ;');
            $ff->execute(array(':id' => (int) $_POST['id2']));

            $ff = $db->prepare('INSERT INTO `mitems-dops` ( `id_item`, `name`, `value` ) values ( :id, \'pay_check\', \'yes\' ) ');
            $ff->execute(array(':id' => (int) $_POST['id2']));

            \f\end2('<div>'
                    . '<nobr>'
                    . '<b class="warn" >отправлено на оплату</b>'
                    . '</nobr>'
                    . '</div>', true);
        }
//
        elseif ($_POST['action'] == 'edit_items_dop') {

//        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'))
//            require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

            $ff = $db->prepare('DELETE FROM `mitems-dops` WHERE `id_item` = :id AND `name` = \'pay_check\' ;');
            $ff->execute(array(':id' => (int) $_POST['id2']));

            $ff = $db->prepare('INSERT INTO `mitems-dops` ( `id_item`, `name`, `value` ) values ( :id, \'pay_check\', \'yes\' ) ');
            $ff->execute(array(':id' => (int) $_POST['id2']));

            \f\end2('<div>'
                    . '<nobr>'
                    . '<b class="warn" >отправлено на оплату</b>'
                    . '</nobr>'
                    . '</div>', true);
        }
    }
//
    catch (\Exception $ex) {

        $e = '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
                . PHP_EOL . $ex->getMessage() . ' #' . $ex->getCode()
                . PHP_EOL . $ex->getFile() . ' #' . $ex->getLine()
                . PHP_EOL . $ex->getTraceAsString()
                . '</pre>';

        \f\end2($e, true);
    } catch (\PDOException $ex) {

        $e = '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
                . PHP_EOL . $ex->getMessage() . ' #' . $ex->getCode()
                . PHP_EOL . $ex->getFile() . ' #' . $ex->getLine()
                . PHP_EOL . $ex->getTraceAsString()
                . '</pre>';

        \f\end2($e, true);
    }
}

//
elseif (isset($_POST['action']) && $_POST['action'] == 'add_new_minus') {
// action=add_new_smena

    try {

//        require_once DR . '/all/ajax.start.php';
//
//        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'))
//            require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
//
//        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/class.php'))
//            require ($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/class.php');

        \Nyos\mod\items::addNew($db, $vv['folder'], \Nyos\nyos::$menu['072.vzuscaniya'], array(
// 'head' => rand(100, 100000),
            'date_now' => date('Y-m-d', strtotime($_REQUEST['date'])),
            'jobman' => $_REQUEST['jobman'],
            'sale_point' => $_REQUEST['salepoint'],
            'summa' => $_REQUEST['summa'],
            'text' => $_REQUEST['text']
        ));


//        if (date('Y-m-d', $start_time) == date('Y-m-d', $fin_time)) {
//            $dd = true;
//        } else {
//            $dd = false;
//        }
//        $r = ob_get_contents();
//        ob_end_clean();


        \f\end2('<div>'
                . '<nobr><b class="warn" >взыскание добавлено</b>'
                . '<br/>'
                . $_REQUEST['summa']
                . '<br/>'
                . '<small>' . $_REQUEST['text'] . '</small>'
//                . (
//                $dd === true ?
//                        '<br/>с ' . date('H:i', $start_time) . ' - ' . date('H:i', $fin_time) : '<br/>с ' . date('Y-m-d H:i:s', $start_time) . '<br/>по ' . date('Y-m-d H:i:s', $fin_time)
//                )
// .'окей '.$b
//                . '</br>'
//                . $b
//                . '</br>'
//                . $r
                . '</nobr>'
                . '</div>', true);
    } catch (\Exception $ex) {

        $e = '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
                . PHP_EOL . $ex->getMessage() . ' #' . $ex->getCode()
                . PHP_EOL . $ex->getFile() . ' #' . $ex->getLine()
                . PHP_EOL . $ex->getTraceAsString()
                . '</pre>';

        \f\end2($e, true);
    } catch (\PDOException $ex) {

        $e = '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
                . PHP_EOL . $ex->getMessage() . ' #' . $ex->getCode()
                . PHP_EOL . $ex->getFile() . ' #' . $ex->getLine()
                . PHP_EOL . $ex->getTraceAsString()
                . '</pre>';

        \f\end2($e, true);
    }
}
//
elseif (isset($_POST['action']) && $_POST['action'] == 'add_new_plus') {
// action=add_new_smena

    try {

//require_once DR . '/all/ajax.start.php';
//        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'))
//            require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
//
//        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/class.php'))
//            require ($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/class.php');

        \Nyos\mod\items::addNew($db, $vv['folder'], \Nyos\nyos::$menu['072.plus'], array(
// 'head' => rand(100, 100000),
            'date_now' => date('Y-m-d', strtotime($_REQUEST['date'])),
            'jobman' => $_REQUEST['jobman'],
            'sale_point' => $_REQUEST['salepoint'],
            'summa' => $_REQUEST['summa'],
            'text' => $_REQUEST['text']
        ));


//        if (date('Y-m-d', $start_time) == date('Y-m-d', $fin_time)) {
//            $dd = true;
//        } else {
//            $dd = false;
//        }
//        $r = ob_get_contents();
//        ob_end_clean();


        \f\end2('<div>'
                . '<nobr><b class="warn" >премия добавлена'
                . '<br/>'
                . $_REQUEST['summa']
                . '<br/>'
                . '<small>' . $_REQUEST['text'] . '</small>'
                . '</b>'
//                . (
//                $dd === true ?
//                        '<br/>с ' . date('H:i', $start_time) . ' - ' . date('H:i', $fin_time) : '<br/>с ' . date('Y-m-d H:i:s', $start_time) . '<br/>по ' . date('Y-m-d H:i:s', $fin_time)
//                )
// .'окей '.$b
//                . '</br>'
//                . $b
//                . '</br>'
//                . $r
                . '</nobr>'
                . '</div>', true);
    } catch (\Exception $ex) {

        $e = '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
                . PHP_EOL . $ex->getMessage() . ' #' . $ex->getCode()
                . PHP_EOL . $ex->getFile() . ' #' . $ex->getLine()
                . PHP_EOL . $ex->getTraceAsString()
                . '</pre>';

        \f\end2($e, true);
    } catch (\PDOException $ex) {

        $e = '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
                . PHP_EOL . $ex->getMessage() . ' #' . $ex->getCode()
                . PHP_EOL . $ex->getFile() . ' #' . $ex->getLine()
                . PHP_EOL . $ex->getTraceAsString()
                . '</pre>';

        \f\end2($e, true);
    }
}
///
elseif (isset($_POST['action']) && $_POST['action'] == 'show_info_strings') {

//    require_once DR . '/all/ajax.start.php';
//
//    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'))
//        require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
//
//    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/all/exception.nyosex'))
//        require $_SERVER['DOCUMENT_ROOT'] . '/all/exception.nyosex';
// require_once DR.'/vendor/didrive_mod/items/class.php';
// \Nyos\mod\items::getItems( $db, $folder )
// echo DR ;
    $loader = new Twig_Loader_Filesystem(dirname(__FILE__) . '/tpl.ajax/');

// инициализируем Twig
    $twig = new Twig_Environment($loader, array(
        'cache' => $_SERVER['DOCUMENT_ROOT'] . '/templates_c',
        'auto_reload' => true
//'cache' => false,
// 'debug' => true
    ));

    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/all/twig.function.php'))
        require ($_SERVER['DOCUMENT_ROOT'] . '/all/twig.function.php');

    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/class.php'))
        require ($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/class.php');

    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/1/twig.function.php'))
        require ($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/1/twig.function.php');

//    \Nyos\Mod\Items::getItems($db, $folder, $module, $stat, $limit);

    $vv['get'] = $_GET;

    $ttwig = $twig->loadTemplate('show_table.htm');
    echo $ttwig->render($vv);

    $r = ob_get_contents();
    ob_end_clean();

// die($r);


    \f\end2('окей', true, array('data' => $r));
}

f\end2('Произошла неописуемая ситуация #' . __LINE__ . ' обратитесь к администратору', 'error');

exit;
