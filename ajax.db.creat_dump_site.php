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


if (!empty($_REQUEST['folder'])) {
    $dir_site = DS . 'sites' . DS . $_REQUEST['folder'] . DS;
} else {
    $dir_site = dir_site;
}

//echo '<br/>'.DR;
//echo '<br/>'.dir_site;



$new_dump_dir = DR . $dir_site;
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

if (1 == 1 && $latest_file_dt > ( $_SERVER['REQUEST_TIME'] - 3600 * 24 )) {
    die('самый свежий дамп создан менее суток назад, не создаём дампа');
}

// echo __FILE__.' '.__LINE__;
// если база mysql
if (isset($db_cfg['type']) && $db_cfg['type'] == 'mysql') {

    $name_file_dump = 'dump.db..' . date('Y-m-d_H.i.s') . '..creat..' . ( $db_cfg['type'] ?? '' ) . '..dump.sql';
    $new_dump = $new_dump_dir . $name_file_dump;

    exec('mysqldump --user=' . $db_cfg['login'] . ' --password=' . $db_cfg['pass'] . ' --host=' . $db_cfg['host'] . ' ' . $db_cfg['db'] . ' > ' . $new_dump);

    if (file_exists($new_dump)) {

        // echo '<br/>' . __LINE__;
        echo '<br/>создали дамп БД';
        // exec('tar -cvf '.$new_dump.'.tar.gz '.$new_dump );
        exec('zip -r -9 -j ' . $new_dump . '.zip ' . $new_dump);

        if (file_exists($new_dump . '.zip') && file_exists($new_dump)) {
            echo '<br/>сжали в архив и удалили оригинал';
            unlink($new_dump);
        }
    } else {
        // echo '<br/>' . __LINE__;
    }
}
// если pdo sqlite
elseif (file_exists(DR . $dir_site . 'db.sqllite.sl3')) {

//    $name_file_dump = 'dump.db..' . date('Y-m-d_H.i.s') . '..creat..' . ( $db_cfg['type'] ?? '' ) . '..dump.sql';
//    $new_dump = $new_dump_dir.$name_file_dump;
    // $name_file_dump = 'dump.db..' . date('Y-m-d_H.i.s') . '..creat..sqlite..dump.sl3';
    $new_dump = DR . $dir_site . 'db.sqllite.sl3';

    // exec('mysqldump --user=' . $db_cfg['login'] . ' --password=' . $db_cfg['pass'] . ' --host=' . $db_cfg['host'] . ' ' . $db_cfg['db'] . ' > ' . $new_dump);
//    if ( file_exists( $new_dump )) {

    $new_dump2 = DR . $dir_site . 'dump.db..' . date('Y-m-d_H.i.s') . '..creat..sqlite..dump.sl3';

    // echo '<br/>' . __LINE__;
    echo '<br/>создали дамп БД';
    // exec('tar -cvf '.$new_dump.'.tar.gz '.$new_dump );
    exec('zip -r -9 -j ' . $new_dump2 . '.zip ' . $new_dump);

//        if (file_exists($new_dump . '.zip') && file_exists($new_dump)) {
//            echo '<br/>сжали в архив и удалили оригинал' ;
    echo '<br/>сжали в архив';
//            unlink($new_dump);
//        }
//    } else {
//        // echo '<br/>' . __LINE__;
//    }
}


// храним 14 свежих файлов остальные удаляем
$n = 0;
foreach ($fitetime_now as $k => $v) {
    $n++;

    if ($n >= 14 && file_exists($v)) {
        echo '<br/>удалили старый файл ' . $v;
        unlink($v);
    }
}



if (empty($vv['info_send_telegram']['admin_ajax_job']) && file_exists(DR . $dir_site . 'config.php')) {
    require ( DR . $dir_site . 'config.php' );
}



if (!empty($vv['info_send_telegram']['admin_ajax_job'])) {
    
    $telega_send .= 'создали дамп БД';
    
    foreach ($vv['admin_ajax_job'] as $k => $v) {
        \nyos\Msg::sendTelegramm($telega_send, $v);
        //\Nyos\NyosMsg::sendTelegramm('Вход в управление ' . PHP_EOL . PHP_EOL . $e, $k );
    }
}


die();
