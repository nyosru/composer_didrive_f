<?php

namespace f;

if (!defined('IN_NYOS_PROJECT'))
    die('<h1>Сработала защита функций v1</h1><p>от злостных розовых хакеров.<br>Приготовтесь к DOS атаке (6 поколения на ip-' . $_SERVER["REMOTE_ADDR"] . ') в течении 30 минут.</p>');

class File {

    /**
     * фильтр, ищем файлы у которых дата меньше указанного unix времени
     * listFileInDir();
     * @var int 
     */
    public static $filtr_date_unix_menee = null;

    /**
     * считаем размер найденных файлов
     * listFileInDir();
     * @var int 
     */
    public static $all_size = 0;

    /**
     * если true то все файлы стираем что нашли
     * listFileInDir();
     * @var bool 
     */
    public static $delete_files = false;

    /**
     * сохранить массив из полученных файлов (все не важно фиьлтр есть или нет)
     * @var type 
     */
    public static $save_dir_files = false;

    /**
     * массив с файлами
     * @var type 
     */
    public static $dir_files = [];

    public static function deleteOldTimeAndAllSize($dir, $day_to_old_photo, $max_size) {

        \f\timer::start(100);
        \f\CalcMemory::start(100);

        self::$delete_files = true;

        $txt2 = '';

        if (!isset($_REQUEST['only_size'])) {
            $txt2 .= 'Ищем файлы что лежат на сервере более ' . $day_to_old_photo . ' суток';
            self::$filtr_date_unix_menee = strtotime(date('Y-m-d 09:00:00', $_SERVER['REQUEST_TIME']) . ' -' . $day_to_old_photo . ' day');
            $file_list = self::listFileInDir($dir, 1);
            $txt2 .= PHP_EOL . 'удалено файлов: ' . sizeof($file_list) . ' (' . round(self::$all_size / 1024 / 1024, 2) . ' Mb)';
        }

        $txt2 .= PHP_EOL . PHP_EOL . 'Проверяем сколько места занято';
        $txt2 .= PHP_EOL . 'Максимум:' . number_format($max_size / 1024 / 1024, 1, '.', '`') . ' Mb';

        $size = self::dirSize($dir);

        $txt2 .= PHP_EOL . 'Занято: ' . number_format($size / 1024 / 1024, 1, '.', '`') . ' Mb';

        if ($size > $max_size) {

            $size_for_delete = $size - $max_size;

            $txt2 .= PHP_EOL . 'Занято больше запланированных'
                    . PHP_EOL . 'Очищаем лишнее (трём самые старые файлы ' . number_format(($size - $max_size) / 1024 / 1024, 1, '.', '`') . ' Mb) )';

            self::$filtr_date_unix_menee = null;
            self::$delete_files = false;
            $file_list = self::listFileInDir($dir, 1);
            usort($file_list, "\\f\\sort_ar_date_int_desc");

            foreach ($file_list as $file) {

                if (!file_exists($file['name']))
                    continue;

                if ($size_for_delete < 0)
                    break;

                if ($size_for_delete > 0) {
                    unlink($file['name']);
                    $size_for_delete -= $file['size'] ?? 0;
                }
            }
        } else {
            $txt2 .= PHP_EOL . 'Занято менее запланированного, ничего не трём';
        }

        $txt2 .= PHP_EOL . '--- обработка --- ' . PHP_EOL . round(\f\timer::stop('str', 100), 3) . ' сек ' . \f\CalcMemory::stop(100) . ' Кб';

        return ['msg' => $txt2];
    }

    /**
     * считаем размер папки
     * @param type $dir
     * @return int
     */
    public static function dirSize($dir) {

        //в эту переменную будем накапливать размеры всех найденных файлов
        $totalsize = 0;
        // echo '<br/>'.__LINE__.' '.$dir;

        if (!is_dir($dir))
            return 0;

        $files = scandir($dir);
        // \f\pa($files);

        foreach ($files as $k => $filename) {

            // если это не сама папка и не её родитель
            if (!isset($filename{2}))
                continue;

            $name = $dir . DS . $filename;
            // echo '<Br/>'.__LINE__.' '.$name;
            //если это папка - уходим в рекурсию и накапливаем её результат
            if (is_dir($name)) {
                // echo '<br/>'. __LINE__.' dir '. $name;
                $totalsize += self::dirSize($name);
            }

            //если это файл - накапливаем его размер
            elseif (file_exists($name)) {
                // echo '<br/>'. __LINE__.' dir '. $name;
                $totalsize += filesize($name);
            }
        }

        //возвращаем накопленное значение размеров
        return $totalsize;
    }

    public static function readXml($file) {

        $return = [];

        $reader = new XMLReader();
        /**
         * указываем ридеру что будем парсить этот файл
         */
        $reader->open($file);

        // циклическое чтение документа
        while ($reader->read()) {

            if ($reader->nodeType == XMLReader::ELEMENT) {

                // если находим элемент <card>
                if ($reader->localName == 'card') {

                    $data = array();

                    // считываем аттрибут number
                    $data['number'] = $reader->getAttribute('number');

                    // читаем дальше для получения текстового элемента
                    $reader->read();

                    if ($reader->nodeType == XMLReader::TEXT) {
                        $data['name'] = $reader->value;
                    }

                    // ну и запихиваем в бд, используя методы нашего адаптера к субд
                    // SomeDataBaseAdapter::insertContact($data);
                    $return[] = $data;
                }
            }
        }

        return $return;
    }

    /**
     * список файлов с размерами
     * ( удаляем все файлы .log )
     * @param type $dir
     * @return int
     */
    public static function listFileInDir($dir, $vers = 1) {

        // echo '<Br/>' . __FUNCTION__ . ' ' . $vers;
        // echo '<br/>f1 ' . $dir;
        //в эту переменную будем накапливать размеры всех найденных файлов
        $total_file = [];
        // echo '<br/>'.__LINE__.' '.$dir;

        if ($vers == 1) {

            //$ww = 0;

            foreach (glob($dir . "*") as $name) {

//                $ww++;
//                if ($ww >= 100)
//                    break;
                // $name = $dir . DS . $file;
                //если это папка - уходим в рекурсию и накапливаем её результат
                if (is_dir($name)) {
                    // echo '<br/>' . __LINE__ . ' dir ' . $name;
                    $t = self::listFileInDir($name . DS, $vers);
                    foreach ($t as $k => $v) {
                        $total_file[] = $v;
                    }
                    unset($t);
                }

                //если это файл - накапливаем его размер
                elseif (file_exists($name)) {

                    if (strpos($name, '.log') !== false) {
                        unlink($name);
                        continue;
                    }

                    // echo '<br/>'. __LINE__.' dir '. $name;
                    // $totalsize += filesize($name);
                    // echo '<br/>' . __LINE__ . ' file ' . $name;

                    $ar = [
                        'name' => $name,
                        'size' => filesize($name),
                        'date' => filemtime($name),
                            // 'dt' => date('Y-m-d H:i:s', $file_time)
                    ];

                    // если флаг стоит то сохраняем все файлы в массиве
                    if (self::$save_dir_files === true) {
                        self::$dir_files[] = $ar;
                    }

                    // echo '<Br/><br/>d1: ' . date('Y-m-d H:i:s', self::$filtr_date_unix_menee) . ' d2: ' . date('Y-m-d H:i:s', $file_time);

                    if (!empty(self::$filtr_date_unix_menee) && $ar['date'] > self::$filtr_date_unix_menee)
                        continue;

                    // echo '<br/>' . __LINE__ . ' file ' . $name;

                    if (self::$delete_files === true) {
                        unlink($name);
                        $ar['file_delete'] = true;
                    }

                    $total_file[] = $ar;

                    self::$all_size += $ar['size'];
                }
            }
        } else {

            if (!is_dir($dir))
                return 0;

            $files = scandir($dir);
            // \f\pa($files);

            foreach ($files as $k => $filename) {

                // если это не сама папка и не её родитель
                if (!isset($filename{2}))
                    continue;

                $name = $dir . DS . $filename;
                // echo '<Br/>'.__LINE__.' '.$name;
                //если это папка - уходим в рекурсию и накапливаем её результат
                if (is_dir($name)) {
                    // echo '<br/>'. __LINE__.' dir '. $name;
                    $t = \f\listFileInDir($name, $vers);
                    foreach ($t as $k => $v) {
                        $total_file[] = $v;
                    }
                    unset($t);
                }

                //если это файл - накапливаем его размер
                elseif (file_exists($name)) {
                    // echo '<br/>'. __LINE__.' dir '. $name;
                    // $totalsize += filesize($name);
                    $total_file[] = [
                        'name' => $name,
                        'size' => filesize($name),
                        'date' => filemtime($name)
                    ];
                }
            }
        }

        //возвращаем накопленное значение размеров
        return $total_file;
    }

}

/**
 * считаем размер папки
 * старая версия, новая в классе \f\File
 */
function dir_size($dir) {

    //в эту переменную будем накапливать размеры всех найденных файлов
    $totalsize = 0;
    // echo '<br/>'.__LINE__.' '.$dir;

    if (!is_dir($dir))
        return 0;

    $files = scandir($dir);
    // \f\pa($files);

    foreach ($files as $k => $filename) {

        // если это не сама папка и не её родитель
        if (!isset($filename{2}))
            continue;

        $name = $dir . DS . $filename;
        // echo '<Br/>'.__LINE__.' '.$name;
        //если это папка - уходим в рекурсию и накапливаем её результат
        if (is_dir($name)) {
            // echo '<br/>'. __LINE__.' dir '. $name;
            $totalsize += \f\dir_size($name);
        }

        //если это файл - накапливаем его размер
        elseif (file_exists($name)) {
            // echo '<br/>'. __LINE__.' dir '. $name;
            $totalsize += filesize($name);
        }
    }

    //возвращаем накопленное значение размеров
    return $totalsize;
}

/**
 * список файлов с размерами
 * @param type $dir
 * @return int
 */
function deleteFileOverSize($dir, $bites) {

    // echo '<br/>dir: '.$dir;

    $e = \f\dir_size($dir);

    // echo '<br/>r1: '.$e;
    //echo '<br/>r2: '. round( $e/1024/1024, 2).' Mb';

    $return = ['size' => $e];
    // \f\pa($return);

    /**
     * сколько Мб должно быть занято после проверки
     */
    // $size_md = 28 * 1024 * 1024 * 1024;
    // $size_md = 6 * 1024 * 1024;
    $size_md = $bites;

    // $return['size'] = 29 * 1024 * 1024 * 1024;

    $udalit = $return['size'] - $size_md;
    // \f\pa($raznica_mb);

    if ($udalit < 0) {
        return \f\end3('норм размер (файлов меньше ограничения)'
                . '<br/>нужно ' . ceil($size_md / 1024 / 1024) . ' mb ... уже есть ' . ceil($return['size'] / 1024 / 1024) . ' mb', false);
        die('<br/>всё норм размер');
    }

    $file_list = \f\listFileInDir($dir);
    // \f\pa($dd, 2);
    usort($file_list, "\\f\\sort_ar_date_int_desc");
    // \f\pa( $file_list, 2);
    // echo '<br/>++ ' . $udalit / 1024 / 1024;

    $tt = 0;

    foreach ($file_list as $k => $v) {

        $tt++;

        $udalit -= $v['size'];

        if ($tt <= 20) {

//            \f\pa($v);
//        echo '<br/>'.__LINE__
//                // .' '.$v['name']
//        // .'<Br/>' 
//        .'кб '.round($v['size']/1024, 3)
//        // .'<Br/>'
//        ;
            // echo '<br/>кб '.$v['size']/1024;
            echo '<br/>' . $v['name'];
            echo '<br/>кб' . $v['size'] / 1024;
            // $raznica_mb += ceil($v['size'] / 1024);
            // echo '<br/>u '.$udalit;
            // \f\pa($raznica_mb);
            // echo '<br/>r: '.$raznica_mb;
        }

        if ($udalit < 0) {
            echo '<br/>удалили сколько нужно, всё норм размер';
            break;
        }
    }

    echo '<br/>будет удалено: файлов ' . $tt;
    echo '<br/>++ ' . $udalit / 1024 / 1024;

    return \f\end3('удалено: файлов ' . $tt, true, ['file_delete' => $tt]);
}

/**
 * удаляем все файлы которые старее указанного unxi времени
 * @param string $dir
 * @param int $unix_time
 * @return type
 */
function deleteFileOverDate(string $dir, int $unix_time) {
    $e = \f\dir_size($dir);

    $return = ['date_del' => date('Y-m-d H:i:s', $unix_time)];

    $file_list = \f\listFileInDir($dir);
    // \f\pa($dd, 2);
    // usort($file_list, "\\f\\sort_ar_date_int_desc");
    // \f\pa( $file_list, 2);
    // echo '<br/>++ ' . $udalit / 1024 / 1024;

    $tt = 0;

    foreach ($file_list as $k => $v) {

        if (empty($v['date']) || $v['date'] >= $unix_time)
            continue;

        $v['dt'] = date('Y-m-d H:i:s', $v['date']);
        \f\pa($v);

//        echo '<br/>' . $v['name'];
//        echo '<br/>date ' . $v['date'];

        $tt++;
    }

//    echo '<br/>будет удалено: файлов ' . $tt;
//    echo '<br/>++ ' . $udalit / 1024 / 1024;

    return \f\end3('удалено ' . $tt . ' файлов', true, ['file_delete' => $tt]);
}

/**
 * список файлов с размерами
 * эта функ старая - используйте новую в классе /f/file
 * @param type $dir
 * @return int
 */
function listFileInDir($dir, $vers = 1) {

    //в эту переменную будем накапливать размеры всех найденных файлов
    $total_file = [];
    // echo '<br/>'.__LINE__.' '.$dir;

    if ($vers == 1) {

        foreach (glob($dir . "*") as $name) {

            // $name = $dir . DS . $file;
            //если это папка - уходим в рекурсию и накапливаем её результат
            if (is_dir($name)) {
                // echo '<br/>'. __LINE__.' dir '. $name;
                $t = \f\listFileInDir($name . DS, $vers);
                foreach ($t as $k => $v) {
                    $total_file[] = $v;
                }
            }

            //если это файл - накапливаем его размер
            elseif (file_exists($name)) {
                // echo '<br/>'. __LINE__.' dir '. $name;
                // $totalsize += filesize($name);
                $total_file[] = [
                    'name' => $name,
                    'size' => filesize($name),
                    'date' => filemtime($name)
                ];
            }
        }
    } else {

        if (!is_dir($dir))
            return 0;

        $files = scandir($dir);
        // \f\pa($files);

        foreach ($files as $k => $filename) {

            // если это не сама папка и не её родитель
            if (!isset($filename{2}))
                continue;

            $name = $dir . DS . $filename;
            // echo '<Br/>'.__LINE__.' '.$name;
            //если это папка - уходим в рекурсию и накапливаем её результат
            if (is_dir($name)) {
                // echo '<br/>'. __LINE__.' dir '. $name;
                $t = \f\listFileInDir($name, $vers);
                foreach ($t as $k => $v) {
                    $total_file[] = $v;
                }
            }

            //если это файл - накапливаем его размер
            elseif (file_exists($name)) {
                // echo '<br/>'. __LINE__.' dir '. $name;
                // $totalsize += filesize($name);
                $total_file[] = [
                    'name' => $name,
                    'size' => filesize($name),
                    'date' => filemtime($name)
                ];
            }
        }
    }

    //возвращаем накопленное значение размеров
    return $total_file;
}

/**
 * проверяем есть ли шаблон во 2 папке, если нет то возвращаем путь из папки 1 
 * @param string $tpl
 * @param string $dir1
 * и после второй папки ищем в этой *tpl*|*tpl*.tpl|*tpl*.htm
 * @param string $dir2
 * сначала смотрим тут файлы *tpl*|*tpl*.tpl|*tpl*.htm
 * @param type $root
 * базовый путь для сравнений
 * @return string 
 * путь до шаблона без рута
 */
function like_tpl(string $tpl, string $dir1, string $dir2, $root = '') {

    if (file_exists($root . $dir2 . $tpl)) {

//        echo PHP_EOL;
//        echo $dir2 . $tpl;
        return $dir2 . $tpl;
    }
    //
    elseif (file_exists($root . $dir2 . $tpl . '.tpl')) {
//        echo PHP_EOL;
//        echo $dir2 . $tpl . '.tpl';
        return $dir2 . $tpl . '.tpl';
    }
    //
    elseif (file_exists($root . $dir2 . $tpl . '.htm')) {
//        echo PHP_EOL;
//        echo $dir2 . $tpl . '.htm';
        return $dir2 . $tpl . '.htm';
    }
    //
    elseif (file_exists($root . $dir1 . $tpl)) {
//        echo PHP_EOL;
//        echo $dir1 . $tpl;
        return $dir1 . $tpl;
    }
    //
    elseif (file_exists($root . $dir1 . $tpl . '.tpl')) {
//        echo PHP_EOL;
//        echo $dir1 . $tpl . '.tpl';
        return $dir1 . $tpl . '.tpl';
    }
    //
    elseif (file_exists($root . $dir1 . $tpl . '.htm')) {
//        echo PHP_EOL;
//        echo $dir1 . $tpl . '.htm';
        return $dir1 . $tpl . '.htm';
    }
    //
    else {
        return false;
    }
}

function readDataFile(string $link_file_data, $type = null) {

    //echo '<br/>' . __FILE__ . ' #' . __LINE__;
    //echo $type;

    if (!file_exists($link_file_data)) {
        throw new \NyosEx('Файл данных не обнаружен ' . $file_data);
    }

    if ($type === null || $type == '1c_win1251' || $type == 'csv') {

        $handle = @fopen($link_file_data, "r");
        $t_head = null;

        $datas = array(
            // массив для заголовков
            'heads' => [],
            // массив для заголовков транслит
            'ht' => [],
            // массив для данных
            'data' => []
        );

        if ($handle) {

            $parsing_start = false;
            if ($type == 'csv') {
                $parsing_start = true;
            }


            while (( $stroka = fgets($handle, 4096)) !== false) {

                //echo '<br/>'.$stroka;

                /**
                 * запускаем старт парсинга
                 */
                if ($parsing_start === false && ( trim($stroka) == '@@@=' || $type == 'csv' )) {
                    $parsing_start = true;
                    continue;
                }

                /**
                 * обрабатываем данные если есть заголовки
                 */
                if ($parsing_start === true) {

//                    if( $type == 'csv' ){
//                    $vars = explode(';', $stroka );
//                    }else{
                    $vars = explode(';', iconv('windows-1251', 'UTF-8', $stroka));
//                    }

                    /**
                     * Получаем заголовки - 1ая строка после старта парсинга
                     */
                    if ($t_head === null) {
                        $t_head = true;

                        foreach ($vars as $k => $v) {
                            $v = trim($v);
                            if (!empty($v)) {
                                $datas['heads'][$k] = $v;
                                $datas['ht'][\f\translit($v, 'uri2')] = $k;
                            }
                        }
                        // \f\pa($heads);

                        continue;
                    }
                    /**
                     * обработка данных после старта и получения заголовков
                     */ else {

                        $r = [];

                        foreach ($vars as $k => $v) {
                            if (isset($datas['heads'][$k])) {
                                $r[] = trim($v);
                            }
                        }

                        $datas['data'][] = $r;
                    }
                }
            }

            // \f\pa($datas);

            if (!feof($handle))
                throw new \NyosEx('Ошибка чтения файла ' . $file_data);

            fclose($handle);

            return $datas;
        }
    }

    return array('data' => $t_all, 'dop' => $t_all_dop);
}

/**
 * копирование содержимого папки в другую папку
 * @param type $path
 * @param type $delete_this_folder
 * удаление текущей папки true/false
 * @return boolean
 */
function blank($directory, $delete_this_directory = false) {

// $show_status = true;

    if (isset($show_status) && $show_status === true) {
        $status = '';
        $_SESSION['status1'] = true;
    }

    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
        global $status;

        $status .= '<fieldset class="status" ><legend>' . __CLASS__ . ' #' . __LINE__ . ' + ' . __FUNCTION__ . '</legend>';
    }






    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
        $status .= '<span class="bot_line">#' . __LINE__ . '</span></fieldset>';

        if (isset($show_status) && $show_status === true)
            echo $status;
    }

    return f\end3($res['summa'], true);
}

/**
 * создание csv файла из массива
 * @global string $status
 * @param type $array
 * @param type $file_uri
 * @return type
 */
function creatCsv2($array, $file_uri) {

// $show_status = true;

    if (isset($show_status) && $show_status === true) {
        $status = '';
        $_SESSION['status1'] = true;
    }

    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
        global $status;

        $status .= '<fieldset class="status" ><legend>' . __CLASS__ . ' #' . __LINE__ . ' + ' . __FUNCTION__ . '</legend>';
    }


    $out = fopen($file_uri, 'w');

    foreach ($array as $e1 => $e2) {
//fputcsv($out, array($item[0],$item[1]));

        $ar = array();

        for ($s = 1; $s <= 30; $s++) {
            $ar[$s] = iconv('UTF-8', 'windows-1251', $e2[$s]);
        }

        fputcsv($out, $ar, ';');
    }

    fclose($out);


    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
        $status .= '<span class="bot_line">#' . __LINE__ . '</span></fieldset>';

        if (isset($show_status) && $show_status === true)
            echo $status;
    }

    return \f\end3('ok', true);
}

/**
 * создание csv файла из массива без кавычек у данных
 * @global string $status
 * @param type $array
 * @param type $file_uri
 * @return type
 */
function creatCsv($array, $file_uri) {

// $show_status = true;

    if (isset($show_status) && $show_status === true) {
        $status = '';
        $_SESSION['status1'] = true;
    }

    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
        global $status;

        $status .= '<fieldset class="status" ><legend>' . __CLASS__ . ' #' . __LINE__ . ' + ' . __FUNCTION__ . '</legend>';
    }

    /*
      $out = fopen($file_uri, 'w');

      foreach ($array as $e1 => $e2) {
      //fputcsv($out, array($item[0],$item[1]));

      $ar = array();

      for ($s = 1; $s <= 30; $s++) {
      $ar[$s] = iconv('UTF-8', 'windows-1251', $e2[$s]);
      }

      fputcsv($out, $ar, ';');
      }

      fclose($out);
     */

    $out = ''; // fopen($file_uri, 'w');

    foreach ($array as $e1 => $e2) {
//fputcsv($out, array($item[0],$item[1]));

        $ar = array();

        for ($s = 1; $s <= 30; $s++) {
            if (isset($e2[$s])) {
                $ar[$s] = iconv('UTF-8', 'windows-1251', $e2[$s]);
            }
        }

        $out .= implode(';', $ar) . ';' . PHP_EOL;
    }

    file_put_contents($file_uri, $out);

    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
        $status .= '<span class="bot_line">#' . __LINE__ . '</span></fieldset>';

        if (isset($show_status) && $show_status === true)
            echo $status;
    }

    return \f\end3('ok', true);
}

/**
 * конверитует файл данных в сриализованный массив и пишет в файл
 * @global string $status
 * @param type $file
 * оригинальный файл
 * @param type $result_file
 * файл для записи результата
 * @param type $type
 * (по умолчанию) 1c-win1251 - 1с выгрузка win1251 кодировка
 * @return type
 */
function convertFileToSerialArray($file, $result_file, $type = '1c-win1251', $str_to_lower = false, $return_array = true, $add_trans_var = true) {

    $handle = @fopen($file, "r");

    $t_head = null;
    $t_all = array();
    $r = false;

    if ($type == '1c-win1251' || $type == '1c-win1251-nohead') {

        if ($handle) {
            $nn = 0;

            if ($type == '1c-win1251-nohead')
                $r = true;

            while (( $stroka = fgets($handle, 4096)) !== false) {
                $nn++;

                $stroka = iconv('windows-1251', 'UTF-8', $stroka);

                if (!isset($stroka{0}))
                    break;

                if ($r === true) {

                    if ($t_head === null) {

                        $head2 = $t_head_dop = $t_head = array();
                        $t = explode(';', $stroka);

                        foreach ($t as $k => $v) {
                            if (isset($v{1})) {

                                $v1 = \f\translit(trim($v));
                                $t_head[$k] = $v1;
                                $head2[$v1] = $v;

//                                if( $add_trans_var === true ){
//                                $head2[$v1.'__t'] = $v;
//                                $t_head[$k.'__t'] = $v1.'__t';
//                                }
                            }
                        }
                    }

//
                    else {

                        $s = explode(';', trim($stroka));
                        $s2 = $s1 = array();

                        foreach ($s as $k => $v) {

                            if (isset($t_head[$k]{1})) {
                                //$s1[mb_strtolower(isset($t_head[$k]) ? $t_head[$k] : $k)] = trim($v);
                                // echo '<Br/>'.$t_head[$k];
//                                if( isset($t_head[$k]) && substr($t_head[$k],0,1) !== 'j' ){
//                                    echo '<Br/>'.__LINE__;
//                                }
                                // если заголовок
                                if (isset($t_head[$k]) && substr($t_head[$k], 0, 1) == 'j') {
                                    $s1[mb_strtolower(isset($t_head[$k]) ? $t_head[$k] : $k)] = trim($v);
                                    $s1[mb_strtolower($t_head[$k]) . '_translit'] = \f\translit(trim($v), 'uri2');
                                }
                                // если не заголовок
                                else {
                                    $s1[mb_strtolower(isset($t_head[$k]) ? $t_head[$k] : $k)] = round(trim($v), 2);
                                }
                            }
                        }

                        $t_all[] = $s1;
                    }
                }

// если ещё не было запусков заголовков
                elseif ($r !== true && substr($stroka, 0, 4) == '@@@=') {
                    //echo '<br/>#' . __LINE__ . ' после этой строчки старт данных';
                    $r = true;
                }
            }
        }

        if (!feof($handle)) {
            echo "Ошибка чтения файла\n";
        }

        fclose($handle);

        if (isset($head2) && sizeof($head2) > 0) {
            file_put_contents($result_file . '.head', serialize($head2));
        }

        if ($type == '1c-win1251') {

            if (sizeof($t_all) > 2) {

                file_put_contents($result_file, serialize($t_all));
            } else {

                $e = \f\convertFileToSerialArray($file, $result_file, '1c-win1251-nohead');

                if (isset($e['status']) && $e['status'] == 'ok') {

                    return \f\end3('файл (второй прогон) с массивом записан', true);
                } else {
                    return \f\end3('что то пошло не так (второй прогон) с массивом ', false);
                }
            }
        }
// $type = '1c-win1251-nohead'
        else {

            if (sizeof($t_all) > 2) {

                file_put_contents($result_file, serialize($t_all));
//\f\pa($t_all, 2, null, 'file_put_contents($result_file, serialize($t_all));');
            }
        }

        return \f\end3('файл с массивом записан', true);
    }

    return f\end3('тип обработки не выбран', false);
}

function convert1сFileToSerialArray($file, $result_file) {
//
//    echo '<br/>#' . __LINE__ . ' f ' . $file;
//    echo '<br/>#' . __LINE__ . ' rf ' . $result_file;
//    echo '<br/>#' . __LINE__ . ' t ' . $type;
//    echo '<br/>';
// $show_status = true;

    if (isset($show_status) && $show_status === true) {
        $status = '';
        $_SESSION['status1'] = true;
    }

    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
        global $status;

        $status .= '<fieldset class="status" ><legend>' . __CLASS__ . ' #' . __LINE__ . ' + ' . __FUNCTION__ . '</legend>';
    }

//echo '<br/>#'.__LINE__;
//    if (file_exists($file)) {
//        echo '<br/>файл данных есть ' . $file;
//    }


    $handle = @fopen($file, "r");

//echo '<br/>#'.__LINE__;    

    $t_head = null;
    $t_all = array();
    $r = false;

    if ($handle) {
        $nn = 0;

        while (( $stroka = fgets($handle, 4096)) !== false) {
            $nn++;

            $stroka = iconv('windows-1251', 'UTF-8', $stroka);

            if (!isset($stroka{2}))
                break;

            // echo '<br/>#' . __LINE__ . ' // ' . $stroka;

            if ($r === true) {

                // echo '<br/>#' . __LINE__ . ' обработка полученной строки';

                if ($t_head === null) {

                    // echo '<br/>#' . __LINE__ . ' обработка заголовка';

                    $t_head_dop = $t_head = array();
                    $t = explode(';', $stroka);

                    foreach ($t as $k => $v) {
                        if (isset($v{1})) {
                            $v1 = \f\translit(trim($v));
                            $t_head[$k] = $v1;
                        }
                    }
                }

//
                else {

                    // echo '<br/>#' . __LINE__ . ' обработка строчки данных';

                    $s = explode(';', trim($stroka));
// f\pa($s);
                    $s2 = $s1 = array();

                    foreach ($s as $k => $v) {

                        if (isset($t_head[$k]{1})) {

// if( $k == 'price' || $k == 'price1' || $k == 'price2' )
// $v = round($v,2);

                            $s1[( isset($t_head[$k]) ? $t_head[$k] : $k )] = $v;
                        }
                    }
// f\pa($s1);
// $s1['id_item'] = $nn;
                    $t_all[] = $s1;

//$t_all[] = self::putKeyArray($t_head, explode(';', $stroka));
                }

// // echo __LINE__.'<br/>';
// echo iconv('windows-1251', 'UTF-8', $buffer) . '<br/>';
            }
// если ещё не было запусков заголовков
            elseif ($r !== true && substr($stroka, 0, 4) == '@@@=') {

                // echo '<br/>#' . __LINE__ . ' после этой строчки старт данных';

                $r = true;
            }
        }
    }

// f\pa( $t_all );
// f\pa( $t_all_dop );

    if (!feof($handle)) {
        echo "Ошибка чтения файла\n";
    }

    fclose($handle);

//    echo '<br/>';
//    echo '<br/>#' . __LINE__;
//    echo '<br/>';
// \f\pa($t_all, 2, null, 't_all');
//    \f\pa($t_head);
//    \f\pa($t_all);



    file_put_contents($result_file, serialize($t_all));
//\f\pa($t_all, 2, null, 'file_put_contents($result_file, serialize($t_all));');

    rename($file, $file . '.delete');

    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
        $status .= '<span class="bot_line">#' . __LINE__ . '</span></fieldset>';

        if (isset($show_status) && $show_status === true)
            echo $status;
    }

    return \f\end3('запсано строк ' . sizeof($t_all), true);
}

function scanFileAndConvertToSerialArray($file, $result_file) {

//    echo '<br/>#' . __LINE__ . ' f ' . $file;
//    echo '<br/>#' . __LINE__ . ' rf ' . $result_file;
//    echo '<br/>#' . __LINE__ . ' t ' . $type;
//    echo '<br/>';
// $show_status = true;

    if (isset($show_status) && $show_status === true) {
        $status = '';
        $_SESSION['status1'] = true;
    }

    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
        global $status;

        $status .= '<fieldset class="status" ><legend>' . __CLASS__ . ' #' . __LINE__ . ' + ' . __FUNCTION__ . '</legend>';
    }



    $handle = @fopen($file, "r");

    while (( $stroka = fgets($handle, 4096)) !== false) {
        if (trim($stroka) == '@@@=') {
            echo 'Есть дата строчка';
        }
    }




    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
        $status .= '<span class="bot_line">#' . __LINE__ . '</span></fieldset>';

        if (isset($show_status) && $show_status === true)
            echo $status;
    }

    return f\end3('тип обработки не выбран', false);
}

/**
 * копирование содержимого папки в другую папку
 * @param type $path
 * @param type $delete_this_folder
 * удаление текущей папки true/false
 * @return boolean
 */
function deleteFolder($directory, $delete_this_directory = false) {

//$show_status = true;

    if (isset($show_status) && $show_status === true) {
        $_SESSION['status1'] = true;
    }

    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
        global $status;

        if (isset($show_status) && $show_status === true)
            $status = '';


        $status .= '<fieldset class="status" ><legend>' . __CLASS__ . ' #' . __LINE__ . ' + ' . __FUNCTION__ . '</legend>';
    }

    if (!defined('DS'))
        define('DS', DIRECTORY_SEPARATOR);

    if (!is_dir($directory)) {
        if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
            $status .= '<span class="bot_line">#' . __LINE__ . '</span></fieldset>';
            if (isset($show_status) && $show_status === true)
                echo $status;
        }
        return \f\end3('Папки |' . $directory . '| не существует', false);
    }



// перебираем всё что есть в папке и удаляем    
    $list_dir = scandir($directory);

    foreach ($list_dir as $i => $name) {

        if ($name == '.' || $name == '..')
            continue;

// если папка
        if (is_dir($directory . DS . $name)) {

            if (isset($_SESSION['status1']) && $_SESSION['status1'] === true)
                $status .= '<Br/>удаляю содержимое папки ' . $directory . DS . $name;

            deleteFolder($directory . DS . $name);

            if (isset($_SESSION['status1']) && $_SESSION['status1'] === true)
                $status .= '<Br/>удаляю папку ' . $directory . DS . $name;
            rmdir($directory . DS . $name);
        }
// если файл
        else {

            if (isset($_SESSION['status1']) && $_SESSION['status1'] === true)
                $status .= '<Br/>удаляю файл ' . $directory . DS . $name;

            unlink($directory . DS . $name);
        }
    }

// удаляем саму папку если нужно
    if ($delete_this_directory === true)
        rmdir($directory);

    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
        $status .= '<span class="bot_line">#' . __LINE__ . '</span></fieldset>';

        if (isset($show_status) && $show_status === true)
            echo $status;
    }

    return \f\end3('папка ' . $directory . ' удалена полностью', true);
}

/**
 * копирование содержимого папки в другую папку
 * @param type $from_path
 * @param type $to_path
 * @return boolean
 */
function copyDirectory($from_path, $to_path) {

    if (!is_dir($from_path))
        return FALSE;

    if (!is_dir($to_path) && is_dir($from_path))
        mkdir($to_path, 0755);

    $open = opendir($from_path);

    while ($file = readdir($open)) {

        if ($file != '.' && $file != '..' && is_dir($from_path . '/' . $file)) {
            if (!is_dir($to_path . '/' . $file))
                mkdir($to_path . '/' . $file, 0755);

            copyDirectory($from_path . '/' . $file, $to_path . '/' . $file);
        } elseif ($file != '.' && $file != '..' && is_file($from_path . '/' . $file)) {
            copy($from_path . '/' . $file, $to_path . '/' . $file);
        }
    }

    closedir($open);

    return true;
}

/**
 * получаем расширение файла
 * @param string filename $f
 * @return string
 */
function get_file_ext($f) {

    return pathinfo($f, PATHINFO_EXTENSION);

//    $info = pathinfo($f);
//    return strtolower($info['extension']);
}

// function newfile( '/roor/dir/', '123.gif' )
function newfile($fold, $file) {

    if (!file_exists($fold . $file)) {
// echo '<br/>'.$fold.$file;
// echo '<br/>'.$file;
        return $file;
    } else {
        $file2 = substr($file, 0, -4) . rand(0, 9) . '.' . get_file_ext($file);
// echo '<br/>'.$file2;

        if (!file_exists($fold . $file2)) {
            return $file2;
        } else {
            return newfile($fold, $file2);
        }
    }
}

function file_force_download_var1($file, $name = false) {
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $file)) {

// сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
// если этого не сделать файл будет читаться в память полностью!
        if (ob_get_level()) {
            ob_end_clean();
        }

// заставляем браузер показать окно сохранения файла
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . str_replace(' ', '_', ( $name === false ? basename($file) : $name)));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($_SERVER['DOCUMENT_ROOT'] . $file));
// читаем файл и отправляем его пользователю
        readfile($_SERVER['DOCUMENT_ROOT'] . $file);
        exit;
    } else {
        die('Приходите ещё');
    }
}

function file_force_download($file, $name = false) {

//echo file_get_contents($_SERVER['DOCUMENT_ROOT'].$file);

    if (file_exists($file)) {
        header('X-Accel-Redirect: ' . $file);
// header('X-SendFile: ' . realpath($file));
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . ( $name === false ? basename($file) : $name ));
        exit;
    }
}

function readTypeImage($file) {

    $mime = mime_content_type($file);

    if ($mime == 'image/jpeg') {
        return "jpg";
    } elseif ($mime == 'image/png') {
        return "png";
    } elseif ($mime == 'image/gif') {
        return "gif";
    }
    return false;
}

function creatAddFolder(string $base, string $addDir) {

    echo '<br/>' . __FILE__ . ' [' . __LINE__ . ']';

    if (!is_dir($base))
        throw new \Exception('Базовая папка указана не верно (не существует)', 3);

    $dirs = explode('/', str_replace('\\', '/', $addDir));

    \f\pa($dirs);
}
