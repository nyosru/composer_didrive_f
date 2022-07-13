<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

//echo '<pre>'; print_r( $_REQUEST ); echo '</pre>'; exit;
date_default_timezone_set("Asia/Yekaterinburg");
// header("Cache-control: public");
$status = '';

//require_once $_SERVER['DOCUMENT_ROOT'] . '/include/exception.php';

define('IN_NYOS_PROJECT', true);

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
// die(\f\pa($_REQUEST));

require(__DIR__ . '/../base/all/ajax.start.php');


try {

    $now['folder'] = \Nyos\Nyos::$folder_now;

    if (empty($now['folder']) && !is_dir(DirSite))
        throw new \NyosEx('нет папки или проблема с путями', 3);

    $_dir1 = DS . 'sites' . DS . \Nyos\Nyos::$folder_now . DS . 'download' . DS;
    $_file1 = $_GET['uri'];
    $_file2 = \f\translit($_file1, 'uri2');
    $img_uri = $_dir1 . $_file1;
    $dir_to_save_renew = DR . $_dir1 . 'renew_img' . DS;

    if (!is_dir($dir_to_save_renew))
        mkdir($dir_to_save_renew, 0755);

    if (!defined('DirSite'))
        define('DirSite', $_SERVER['DOCUMENT_ROOT'] . '/sites/' . \Nyos\Nyos::$folder_now);

    // }

    if (!defined('DS'))
        define('DS', DIRECTORY_SEPARATOR);

    if (isset($_REQUEST['uri']) && strpos($_REQUEST['uri'], '.png')) {
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $_REQUEST['uri'])) {
            header("Content-type: image/png");
            die(file_get_contents($_SERVER['DOCUMENT_ROOT'] . $_REQUEST['uri']));
        }
    }

    // если нет картинка
    if (isset($_dir1) && isset($_file1) && !file_exists(DR . $img_uri) && strpos($_file1, '@2x') !== false)
        $_file1 = strtr($_file1, '@2x', '');


    $img_file_mini = 'renew_' . ($_GET['type'] ?? 't') . ($_GET['w'] ?? 'w') . \f\translit($_GET['uri'], 'uri2') . '.jpg';

    if (file_exists($dir_to_save_renew . $img_file_mini)) {
        $mime = mime_content_type($dir_to_save_renew . $img_file_mini);
        header('Content-type: ' . $mime);
        readfile($dir_to_save_renew . $img_file_mini);
        exit;
    }

    // если нет картинка
    if (!isset($_dir1) || !isset($_file1) || !file_exists(DR . $img_uri))
        throw new \Exception('нет изображения [' . DR . $img_uri . ']', 3);

    // если картинка слишком большая
    if (file_exists(DR . $img_uri) && filesize(DR . $img_uri) > 1024 * 1024 * 10)
        throw new \Exception('изображение слишком большое для обработки [' . DR . $img_uri . '] '
            . round(filesize(DR . $img_uri) / 1024 / 1024, 2)
            . ' Mb', 5);

    $ext = \f\get_file_ext($_GET['uri']);
    $mime = mime_content_type(DR . $img_uri);

    if ($mime == 'image/jpeg') {

        $img_file_mini = 'renew_' . ($_GET['type'] ?? 't') . ($_GET['w'] ?? 'w') . \f\translit($_GET['uri'], 'uri2') . '.jpg';
        if (file_exists($dir_to_save_renew . $img_file_mini)) {
            header('Content-type: ' . $mime);
            readfile($dir_to_save_renew . $img_file_mini);
            exit;
        }
    } else {
        // echo $ftype = get_mime_type() filetype( DR . $_dir1 . DS . $_file1 );
        // header('Content-type: image/'.$ext);
        die('неверный тип');
        header('Content-type: ' . $mime);
        readfile(DR . $img_uri);
        exit;
    }

    \Nyos\nyos_image::readImage(DR . $img_uri);

    // режем квадрат из изображения с определённой длинной стороны
    if (isset($_GET['type']) && $_GET['type'] == 'min' && !empty($_GET['w']) && is_numeric($_GET['w'])) {
        //$_GET['w_min'] = 300;
        \Nyos\nyos_image::creatThumbnailProporcii(\Nyos\nyos_image::$image, $_GET['w']);
    }
    // режем квадрат из изображения с определённой длинной стороны
    elseif (isset($_GET['type']) && $_GET['type'] == 'fix_w' && !empty($_GET['w']) && is_numeric($_GET['w'])) {
        //$_GET['w_min'] = 300;
        \Nyos\nyos_image::creatThumbnailProporcii(\Nyos\nyos_image::$image, $_GET['w']);
    }
    // квадрат и всё новое
    elseif (isset($_GET['type']) && $_GET['type'] == 'kv_fill' && !empty($_GET['w']) && is_numeric($_GET['w'])) {
        \Nyos\nyos_image::creatKvadrat($_GET['w']);
    }

    header('Content-type: ' . \Nyos\nyos_image::$mime);

    imagejpeg(\Nyos\nyos_image::$image, $dir_to_save_renew . $img_file_mini);
    imagedestroy(\Nyos\nyos_image::$image);

    die(readfile($dir_to_save_renew . $img_file_mini));
} catch (\Exception $e) {
    header('Content-Type: image/jpeg');
    die(file_get_contents($_SERVER['DOCUMENT_ROOT'] . DS . 'img' . DS . 'no-image.jpg'));
}
