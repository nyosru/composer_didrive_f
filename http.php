<?php

namespace f;

//if (!defined('IN_NYOS_PROJECT'))
//    die('<h1>Сработала защита функций v1</h1><p>от злостных розовых хакеров.<br>Приготовтесь к DOS атаке (6 поколения на ip-' . $_SERVER["REMOTE_ADDR"] . ') в течении 30 минут.</p>');

/**
 * редирект на указанный адрес
 * @param type $host
 * домен или /
 * @param type $file
 * файл
 * @param type $request
 * массив переменных для формирования строки запроса
 * @return
 */
function redirect($host = '/', $file = 'index.php', $request = null) {
    header('Location: '
            . ( !empty($host) ? $host : '' )
            . ( !empty($file) ? $file : '' )
            . ( ( !empty($request) && sizeof($request) > 0 ) ? '?' . http_build_query($request) : '' )
    );
    die();
    return;
}

/**
 * получаем данные с https с помощью curl
 * @param string $uri
 * @return type
 */
function get_curl_https_uri(string $uri) {

    if ($ch = curl_init()) { //инициализация сеанса
        // 
        // $postfields = array('field1' => 'value1', 'field2' => 'value2');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // curl_setopt($ch, CURLOPT_POST, 1);
        // Edit: prior variable $postFields should be $postfields;
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
        $result = curl_exec($ch);
        // \f\pa($result);
        curl_close($ch);
        return $result;
    }
    return \f\end3('что то не то ' . __FUNCTION__, false);
}
