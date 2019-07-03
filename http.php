<?php

namespace f;

if (!defined('IN_NYOS_PROJECT'))
    die('<h1>Сработала защита функций v1</h1><p>от злостных розовых хакеров.<br>Приготовтесь к DOS атаке (6 поколения на ip-' . $_SERVER["REMOTE_ADDR"] . ') в течении 30 минут.</p>');

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
            . ( isset($host{0}) ? $host : '' )
            . ( isset($file{0}) ? $file : '' )
            . ( ( isset($request) && sizeof($request) > 0 ) ? '?' . http_build_query($request) : '' )
    );
    die();
    return;
}
