<?php

namespace nyos\f;

//use Smarty as Smarty;

// строчки безопасности

//if (!defined('IN_NYOS_PROJECT'))
//    die('Сработала защита <b>функций MySQL</b> от злостных розовых хакеров.' .
//            '<br>Приготовтесь к DOS атаке (6 поколения на ip-' . $_SERVER["REMOTE_ADDR"] . ') в течении 30 минут.');

/**
 * проверяем есть ли шаблон во 2 папке, если нет то возвращаем путь из папки 1 
 * @param type $tpl
 * @param type $dir_mod
 * папка модуля в папке модуля сайта 0.site/exe/
 * проверяем её второй
 * @param type $dir_site
 * папка модуля в папке сайта
 * проверяем её первой
 */
function like_tpl($tpl, $dir1, $dir2, $dir_base = null ) {

    if (file_exists( $dir_base . $dir2 . $tpl)) {
        return $dir2 . $tpl;
    }
    //
    elseif (file_exists( $dir_base .  $dir2 . $tpl . '.tpl')) {
        return $dir2 . $tpl . '.tpl';
    }
    //
    elseif (file_exists( $dir_base . $dir2 . $tpl . '.htm')) {
        return $dir2 . $tpl . '.htm';
    }
    //
    elseif (file_exists( $dir_base . $dir1 . $tpl)) {
        return $dir1 . $tpl;
    }
    //
    elseif (file_exists( $dir_base . $dir1 . $tpl . '.tpl')) {
        return $dir1 . $tpl . '.tpl';
    }
    //
    elseif (file_exists( $dir_base . $dir1 . $tpl . '.htm')) {
        return $dir1 . $tpl . '.htm';
    }
    //
    else {
        return false;
    }
}

/**
 * компилируем html шаблон смарти и выводим
 * @param type $template
 * @param type $vars
 * @param type $tpl_dir
 * папка с шаблонами (необязательно)
 * @return type
 */
/*
function compileSmarty($template, $vars, $tpl_dir = null) {

    if (!class_exists('Smarty')) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . DS . 'php' . DS . 'smarty3.1.29' . DS . 'Smarty.class.php' ); // название файла библиотеки
    }

    $smarty = new Smarty();

    if ($tpl_dir !== null)
        $smarty->template_dir = $tpl_dir;

    if (sizeof($vars) > 0)
        foreach ($vars as $k => $v)
            $smarty->assign($k, $v);

    ob_start();
    $smarty->display($template);
    $return = ob_get_contents();
    ob_end_clean();

    return $return;
}
*/