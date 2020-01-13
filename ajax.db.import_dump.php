<?php

die('не готовая штука');

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




$dir1 = DR . DS . 'sites' . DS;
$dirs = scandir($dir1);

$res = [];

foreach ($dirs as $v) {

    if (!isset($v{3}))
        continue;

    if (!is_dir($dir1 . $v))
        continue;

    $u = [
        'folder' => $v,
        'path' => $dir1 . $v . DS,
        'dumps' => []
    ];

    $dirs2 = scandir($dir1 . $v);

    foreach ($dirs2 as $v2) {

        if (strpos($v2, 'dump.db..') !== false)
            $u['dumps'][$v2] = 1;
    }

    // \f\pa($u);

    $u_all[$v] = $u;


//    if ( isset($v{3}) && is_dir( $u['path'] ) ) {
//
//        if ($curl = curl_init()) { //инициализация сеанса
//            
//            // $curl
//            // curl_setopt($curl, CURLOPT_URL, 'http://webcodius.ru/'); //указываем адрес страницы
//            //указываем адрес страницы
//            curl_setopt($curl, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . '/vendor/didrive/f/ajax.db.creat_dump_site.php?' . http_build_query($u));
//            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//            // curl_setopt ($curl, CURLOPT_POST, true);
//            // curl_setopt ($curl, CURLOPT_POSTFIELDS, "i=1");
//            curl_setopt($curl, CURLOPT_HEADER, 0);
//            $res[$v] = 
//            // $result = 
//            curl_exec($curl); //выполнение запроса
//            curl_close($curl); //закрытие сеанса
//        }
//// echo '</div>';
//    }
}

// \f\pa($res);
\f\pa($u_all, 2);

echo '<form action="" method="post" >

<select name="file" >
    <option>выберите</option>';

foreach ($u_all as $k => $v) {
    if (!empty($v['dumps']))
        foreach ($v['dumps'] as $file => $d) {
            echo '<option value="' . $v['path'] . $file . '" >' . $v['folder'] . ' - ' . $file . '</option>';
        }
}

echo '</select>
    <br/>
<button type="submit" >Импортировать</button>

</form>';


if (isset($_POST['file'])) {

    \f\pa($_POST['file']);

    $scan10 = scandir(dirname($_POST['file']));
    $scan1 = scandir(dirname(__FILE__));
    
   $result = array();
    exec('unzip '.$_POST['file']  , $result, $returnval);
    \f\pa($result,'','','res');
    \f\pa($returnval,'','','ret2');    
    
    /*
    $zip = new ZipArchive;
    if ($zip->open( $_POST['file'] ) === TRUE) {

        // путь к каталогу, в который будут помещены файлы
        $zip->extractTo( dirname(__FILE__) );
        $zip->close();

        // удача
    } else {
        // неудача
    }
*/
    
    
    $scan2 = scandir(dirname(__FILE__));
    $ee = array_intersect($scan1, $scan2);
    \f\pa($ee);

    $scan20 = scandir(dirname($_POST['file']));
    $ee2 = array_intersect($scan10, $scan20);
    \f\pa($ee2,'','','2');

}

//    if (1 == 1 && class_exists('\Nyos\Msg'))
//        \Nyos\Msg::sendTelegramm( 'создали дампы БД всех сайтов' , null, 1);

die();

\f\end2('Произошла неописуемая ситуация #' . __LINE__ . ' обратитесь к администратору', 'error');

exit;
