<?php

namespace nyos\f;

class text {

    public function __construct() {
        echo '<br/>start ' . __CLASS__ . '<br/>';
        echo __FILE__ . ' [' . __LINE__ . ']<br/>';
    }

    /**
     * 
     * @param type $sString
     * @param type $sEncoding
     * @return string
     */
    public static function strToHexByRtf($sString, $sEncoding = 'utf-8') {

        //echo $sString.' ';

        if (is_array($sString)) {
            //\f\pa($sString);
            return '';
        }

        $sString = iconv($sEncoding, 'windows-1251', $sString);

        $sString = preg_replace("/([a-zA-Z0-9]{2})/", "\'$1", bin2hex($sString));
        return $sString;
    }

    public static function object_to_array($data) {

        if (is_array($data) || is_object($data)) {
            $result = array();
            foreach ($data as $key => $value) {

                if (isset($_SESSION['status1']) && $_SESSION['status1'] === true)
                    $status .= $key;

                $result[$key] = object_to_array($value);
            }

            if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
                $status .= '</fieldset>';
            }

            return $result;
        }

        return $data;
    }

    /**
     * 
     * @param type $a
     * @return string
     */
    public static function pa2($a) {

        $return = '';

        if (sizeof($a) > 0) {
            $return = 'array:' . sizeof($a)
                    . '<div style="max-height:150px;overflow:auto;" >'
                    . '<ul>';
            foreach ($a as $k => $v) {
                $return .= '<li>';

                if (is_array($v)) {
                    $v1 = (array) $v;
                }

                if (isset($v1) && sizeof($v1) > 0 && is_array($v1)) {
                    $return .= $k . ' // ' . pa2($v1);
                } else {
                    $return .= $k . ' - ' . $v;
                }
                $return .= '</li>';
            }
            $return .= '</ul>';
            $return .= '</div>';
        }

        return $return;
    }

    /**
     * вывод массива
     * @global type $status
     * @param type $g
     * @param type $type
     * html / 2
     * @return type
     */
    public static function pa($g, $type = null, $type2 = null, $name = null) {

        if ($type == 'html' || $type2 == 'html') {
            ob_start('ob_gzhandler');
        }

        if (isset($name{1})) {
            echo '<br/><b>' . $name . '</b>';
        }

        // $trace = debug_backtrace();    
        // echo '<pre>'; print_r($trace); echo '</pre>';

        if ($type == 3) {
            $r = rand(1, 9999);
            echo '<button class="btn btn-info" onclick="$(\'#asdqe' . $r . '\').toggle(\'slow\');" >показать</button>'
            . '<pre id="asdqe' . $r . '" style="display:none;max-height:500px;" >';
        } else if ($type == 2) {
            echo '<pre style="border: 1px solid green; padding: 20px; display:block; overflow: auto; max-height:300px;" >';
        } else {
            echo '<pre>';
        }

        print_r($g);
        echo '</pre>';

        if ($type == 'html' || $type2 == 'html') {
            $r = ob_get_contents();
            ob_end_clean();
        }

        if ($type == 'html_array') {
            return $r;
        }
    }

    /**
     * сокращение номера телефона до обрезанной чистой формы ( 0-087-876-87-87 ) 0 - нет цифры
     * @param type $gsm
     * @param type $type
     * @return boolean
     */
    public static function gsm($gsm, $type = FALSE) {

        $value = preg_replace('/[^\d]+/', '', $gsm); // заменить все символы кроме чисел на ''
        //return $value;

        if ($type == 'all3452') {
            if (isset($value{5}) && !isset($value{6})) {

                return $value;
            } elseif (isset($value{10}) && !isset($value{11}) &&
                    ( substr($value, 0, 5) == 83452 || substr($value, 0, 5) == 73452 )) {

                return substr($value, 5, 6);
            } elseif (isset($value{9}) && !isset($value{10}) &&
                    ( substr($value, 0, 4) == 3452 )) {

                return substr($value, 4, 6);
            } elseif (isset($value{10}) && !isset($value{11}) &&
                    ( substr($value, 0, 2) == 89 || substr($value, 0, 2) == 79 )) {

                return substr($value, 2, 9);
            } elseif (isset($value{9}) && !isset($value{10}) &&
                    substr($value, 0, 1) == 9) {

                return substr($value, 1, 9);
            }
        } elseif ($type === FALSE) {

            if (isset($value{10}) && !isset($value{11}) &&
                    // ( substr($value,0,2) == 89 || substr($value,0,2) == 79 ) )
                    ( substr($value, 0, 1) == 8 || substr($value, 0, 1) == 7 )
            ) {

                return substr($value, 1, 10);
            }

            if (isset($value{9}) && !isset($value{10}) &&
                    substr($value, 0, 1) == 9) {

                return substr($value, 1, 9);
            }
        } elseif ($type == 'search') {

            return $value;
        }

        return false;
    }

    /**
     * перевод номера в удобоваримую форму
     * @param type $gsm3
     * @param type $type
     * @return boolean
     */
    public static function gsm_rus($gsm3, $type = false) {

        // echo '+ ==='.$gsm3.'=<br/>';
        $gsm2 = ereg_replace('[^0-9]', '', $gsm3);

        // echo '++==='.$gsm2.'=<br/>';

        if (is_numeric($gsm2)) {
            //echo '+ ==='.$gsm.'=';

            if (isset($gsm2{8}) && !isset($gsm2{9}) && is_numeric($gsm2)) {
                // echo __LINE__.'<br/>';
                $gsm = $gsm2;
            } elseif (isset($gsm2{9}) && !isset($gsm2{10}) &&
                    ( substr($gsm2, 0, 2) == '89' || substr($gsm2, 0, 2) == '79' )) {
                // echo __LINE__.'<br/>';
                $gsm = substr($gsm2, 2, 9);
            } elseif (isset($gsm2{9}) && !isset($gsm2{10}) && ( substr($gsm2, 0, 1) == 9 || substr($gsm2, 0, 2) == 89 || substr($gsm2, 0, 2) == 79
                    )
            ) {
                // echo __LINE__.'<br/>';
                return '8(' . substr($gsm2, 0, 3) . ')' . substr($gsm2, 3, 3) . '-' . substr($gsm2, 6, 2) . '-' . substr($gsm2, 8, 2);
            } elseif (isset($gsm2{9}) && !isset($gsm2{10})) {
                // echo __LINE__.'<br/>';
                return '8(' . substr($gsm2, 0, 4) . ')' . substr($gsm2, 4, 2) . '-' . substr($gsm2, 6, 2) . '-' . substr($gsm2, 8, 2);
            } elseif (isset($gsm2{9}) && !isset($gsm2{10}) &&
                    substr($gsm2, 0, 1) == 9) {
                // echo __LINE__.'<br/>';
                $gsm = substr($gsm2, 1, 10);
            } elseif (isset($gsm2{5}) && !isset($gsm2{6})) {
                // echo __LINE__.'<br/>';
                // $gsm = substr($gsm2,0,10);
                if ($type == 7) {

                    return '73452' . substr($gsm2, 0, 2) . substr($gsm2, 2, 2) . substr($gsm2, 4, 2);
                } else {

                    return '8(3452)' . substr($gsm2, 0, 2) . '-' . substr($gsm2, 2, 2) . '-' . substr($gsm2, 4, 2);
                }
            } else {

                return '++' . $gsm2;
            }

            //echo '==='.$gsm.'=';
            //echo '8(9'.substr($gsm,0,2).')'.substr($gsm,2,3).'-'.substr($gsm,5,2).'-'.substr($gsm,7,2).'<br/>';
            //if( isset($gsm{8}) && !isset($gsm{9}) )
            //return '8(9'.substr($gsm,0,2).')'.substr($gsm,2,3).'-'.substr($gsm,5,2).'-'.substr($gsm,7,2);
            if ($type == 7) {


                return ( isset($gsm{4}) ) ? '79' . substr($gsm, 0, 2) . substr($gsm, 2, 3) . substr($gsm, 5, 2) . substr($gsm, 7, 2) : false;
            } else {


                return ( isset($gsm{4}) ) ? '8(9' . substr($gsm, 0, 2) . ')' . substr($gsm, 2, 3) . '-' . substr($gsm, 5, 2) . '-' . substr($gsm, 7, 2) : false;
            }
        }

        return false;
    }

    /**
     *
     * @param string $cyr_str
     * @param string $type 
     * uri - замена знаков препинания и прочих скобок на пусто и подчёркивание
     * uri2 - значкки в пустои п одчёркивание буквы в транслит
     * cifr - только цифры на выходе
     * cifr2 - только цифры, вместо запятой точка
     * cifr21 - только цифры, вместо запятой точка, округлено до целых в большую сторону
     * иначе просто транслит
     * @return string
     */
    public static function translit($cyr_str, $type = false) {

        if ($type == 'uri') {
            $cyr_str = strtolower($cyr_str);
            $tr = array(
                '"' => '',
                '\'' => '',
                '-' => '_',
                '(' => '',
                ')' => '',
                '|' => '',
                '.' => '_',
                ' ' => '_',
                '#' => '',
                '№' => '',
                '”' => ''
            );

            return strtr($cyr_str, $tr);
        }
        //
        elseif ($type == 'uri2') {
            //echo $cyr_str.' -- ';
            $cyr = mb_strtolower($cyr_str, 'UTF-8');
            $tr = array(
                '"' => '',
                '\'' => '',
                '/' => '',
                ' ' => '_',
                '-' => '_',
                '[' => '',
                ']' => '',
                ',' => '_',
                '(' => '',
                ')' => '',
                '|' => '',
                '.' => '_',
                '”' => '',
//'q' => 'q',
//'a' => 'a',
//'z' => 'z',
//'w' => 'w',
//'s' => 's',
//'x' => 'x',
//'e' => 'e',
//'d' => 'd',
//'c' => 'c',
//'r' => 'r',
//'f' => 'f',
//'v' => 'v',
//'t' => 't',
//'g' => 'g',
//'b' => 'b',
//'y' => 'y',
//'h' => 'h',
//'n' => 'n',
//'u' => 'u',
//'j' => 'j',
//'m' => 'm',
//'i' => 'i',
//'k' => 'k',
//'o' => 'o',
//'l' => 'l',
//'p' => 'p',
                "а" => "a", "б" => "b", "в" => "v", "г" => "g",
                "д" => "d", "е" => "e", "ж" => "zh",
                "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l",
                "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
                "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
                "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "",
                "ы" => "yi", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya");
            //echo $cyr.' == ';
            //echo strtr($cyr,$tr).'<br/>';

            $c = preg_replace('/[^a-zA-Z0-9_]/', '', mb_strtolower(strtr($cyr, $tr)));

            if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
                $status .= '</fieldset>';
            }

            return $c;
        }
        //
        elseif ($type == 'cifr') {
            //echo $cyr_str.' -- ';

            if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
                $status .= '</fieldset>';
            }

            return preg_replace('/[^0-9]/', '', $cyr_str);
        }
        //
        elseif ($type == 'cifr2') {
            //echo $cyr_str.' -- ';
            $e = preg_replace('/[^0-9,.]/', '', $cyr_str);

            if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
                $status .= '</fieldset>';
            }

            return str_replace(",", ".", $e);
        }
        //
        elseif ($type == 'cifr21') {
            //echo $cyr_str.' -- ';
            $e = preg_replace('/[^0-9,.]/', '', $cyr_str);

            if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
                $status .= '</fieldset>';
            }

            return ceil(str_replace(",", ".", $e));
        }
        //
        else {
            $tr = array(' ' => '_', "Ґ" => "G", "Ё" => "YO", "Є" => "E", "Ї" => "YI", "І" => "I", "і" => "i", "ґ" => "g", "ё" => "yo", "№" => "#", "є" => "e",
                "ї" => "yi", "А" => "A", "Б" => "B", "В" => "V", "Г" => "G", "Д" => "D", "Е" => "E", "Ж" => "ZH", "З" => "Z", "И" => "I",
                "Й" => "Y", "К" => "K", "Л" => "L", "М" => "M", "Н" => "N", "О" => "O", "П" => "P", "Р" => "R", "С" => "S", "Т" => "T",
                "У" => "U", "Ф" => "F", "Х" => "H", "Ц" => "TS", "Ч" => "CH", "Ш" => "SH", "Щ" => "SCH", "Ъ" => "'", "Ы" => "YI", "Ь" => "",
                "Э" => "E", "Ю" => "YU", "Я" => "YA", "а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ж" => "zh",
                "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l", "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
                "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h", "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "'",
                "ы" => "yi", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya");

            $c = preg_replace('/[^a-zA-Z0-9_]/', '', strtr(strtolower($cyr_str), $tr));

            return $c;
        }
    }

    public static function getTimer($date, $time = null) {

        if ($date == date('Y-m-d', $_SERVER['REQUEST_TIME'])) {
            $r = 'сегодня';
        } elseif ($date == date('Y-m-d', $_SERVER['REQUEST_TIME'] - 24 * 3600)) {
            $r = 'вчера';
        } elseif ($date == date('Y-m-d', $_SERVER['REQUEST_TIME'] - 24 * 3600 * 2)) {
            $r = 'позавчера';
        } else {

            $r = ceil(substr($date, 8, 2)) . ' ' . f\date_in_text(ceil(substr($date, 5, 2)), 'месяца');
        }

        if (isset($time{5})) {

            $r .= ' в ' . substr($time, 0, 5);
        }

        return $r;
    }

    public static function date_ru($name_den) {

        if ($name_den == 'Thu') {
            return 'Чт';
        } elseif ($name_den == 'Fri') {
            return 'Пт';
        } elseif ($name_den == 'Sat') {
            return 'Сб';
        } elseif ($name_den == 'Sun') {
            return 'Вс';
        } elseif ($name_den == 'Mon') {
            return 'Пн';
        } elseif ($name_den == 'Tue') {
            return 'Вт';
        } elseif ($name_den == 'Wed') {
            return 'Ср';
        } else {
            return false;
        }
    }

    /**
     * проверка даты
     * 2 послезавтра
     * 1 завтра
     * 0 сегодня
     * -1 вчера
     * -2 позавчера
     * @param type $dat
     * @param type $types
     * text - вывлжим текстом по русски
     * @return boolean
     */
    public static function checkdates($dat, $types = false) {

        //echo $dat.' - '.date('Y-m-d',$_SERVER['REQUEST_TIME']).'<br/>';

        if ($dat == date('Y-m-d', $_SERVER['REQUEST_TIME'])) {
            return $types == 'text' ? 'Сегодня' : 0;
        } elseif ($dat == date('Y-m-d', $_SERVER['REQUEST_TIME'] - 3600 * 24)) {
            return $types == 'text' ? 'Вчера' : -1;
        } elseif ($dat == date('Y-m-d', $_SERVER['REQUEST_TIME'] - 3600 * 48)) {
            return $types == 'text' ? 'Позавчера' : -2;
        } elseif ($dat == date('Y-m-d', $_SERVER['REQUEST_TIME'] + 3600 * 24)) {
            return $types == 'text' ? 'Завтра' : 1;
        } elseif ($dat == date('Y-m-d', $_SERVER['REQUEST_TIME'] + 3600 * 48)) {
            return $types == 'text' ? 'Послезавтра' : 2;
        }

        return false;
    }

    /**
     *  дату в текст
     * @param type $var
     * цифра месяца или номер дня недели
     * @param type $chto
     * // месяца
     * // месяц
     * // день недели кр
     * // день недели
     * @return boolean|string
     */
    public static function date_in_text($var, $chto = null) {

        if (is_numeric($var))
            $var = round($var, 0);

        if ($chto == 'месяца') {
            $tekst[] = 'октября';
            $cifra[] = 10;
            $tekst[] = 'ноября';
            $cifra[] = 11;
            $tekst[] = 'декабря';
            $cifra[] = 12;
            $tekst[] = 'января';
            $cifra[] = 1;
            $tekst[] = 'февраля';
            $cifra[] = 2;
            $tekst[] = 'марта';
            $cifra[] = 3;
            $tekst[] = 'апреля';
            $cifra[] = 4;
            $tekst[] = 'мая';
            $cifra[] = 5;
            $tekst[] = 'июня';
            $cifra[] = 6;
            $tekst[] = 'июля';
            $cifra[] = 7;
            $tekst[] = 'августа';
            $cifra[] = 8;
            $tekst[] = 'сентября';
            $cifra[] = 9;
            $str = str_replace($cifra, $tekst, $var);
            return $str;
        } 
        //
        elseif ($chto == 'месяц') {
            $tekst[] = 'октябрь';
            $cifra[] = 10;
            $tekst[] = 'ноябрь';
            $cifra[] = 11;
            $tekst[] = 'декабрь';
            $cifra[] = 12;
            $tekst[] = 'январь';
            $cifra[] = 1;
            $tekst[] = 'февраль';
            $cifra[] = 2;
            $tekst[] = 'март';
            $cifra[] = 3;
            $tekst[] = 'апрель';
            $cifra[] = 4;
            $tekst[] = 'май';
            $cifra[] = 5;
            $tekst[] = 'июнь';
            $cifra[] = 6;
            $tekst[] = 'июль';
            $cifra[] = 7;
            $tekst[] = 'август';
            $cifra[] = 8;
            $tekst[] = 'сентябрь';
            $cifra[] = 9;
            $str = str_replace($cifra, $tekst, $var);
            return $str;
        } 
        //
        elseif ($chto == 'день недели кр') {
            switch ($var) {
                case "Mon": return 'Пн';
                    break;
                case 'Monday': return 'Пн';
                    break;
                case 'Tue': return 'Вт';
                    break;
                case 'Tuesday': return 'Вт';
                    break;
                case 'Wed': return 'Ср';
                    break;
                case 'Wednesday': return 'Ср';
                    break;
                case 'Thu': return 'Чт';
                    break;
                case 'Thursday': return 'Чт';
                    break;
                case 'Fri': return 'Пт';
                    break;
                case 'Friday': return 'Пт';
                    break;
                case "Sat": return 'Сб';
                    break;
                case 'Saturday': return 'Сб';
                    break;
                case 'Sun': return 'Вс';
                    break;
                case 'Sunday': return 'Вс';
                    break;
            }
        } 
        //
        elseif ($chto == 'день недели') {
            $tekst = $cifra = array();
            if (strlen($var) == 3) {
                $tekst[] = 'Понедельник';
                $cifra[] = 'Mon';
                $tekst[] = 'Вторник';
                $cifra[] = 'Tue';
                $tekst[] = 'Среда';
                $cifra[] = 'Wed';
                $tekst[] = 'Четверг';
                $cifra[] = 'Thu';
                $tekst[] = 'Пятница';
                $cifra[] = 'Fri';
                $tekst[] = 'Суббота';
                $cifra[] = 'Sat';
                $tekst[] = 'Воскресенье';
                $cifra[] = 'Sun';
            } else {
                $tekst[] = 'Понедельник';
                $cifra[] = 'Monday';
                $tekst[] = 'Вторник';
                $cifra[] = 'Tuesday';
                $tekst[] = 'Среда';
                $cifra[] = 'Wednesday';
                $tekst[] = 'Четверг';
                $cifra[] = 'Thursday';
                $tekst[] = 'Пятница';
                $cifra[] = 'Friday';
                $tekst[] = 'Суббота';
                $cifra[] = 'Saturday';
                $tekst[] = 'Воскресенье';
                $cifra[] = 'Sunday';
            }
            $str = str_replace($cifra, $tekst, $var);
            return $str;
        } 
        //
        else {
            return false;
        }
    }

    public static function txt_okonchanie($cifra, $type = 'ов') {

        if ($type == 'ов') {

            $okon = substr($cifra, -1);
            if (
                    $cifra == 1 || ( $cifra > 20 && $okon == 1 )
            ) {
                return '';
            } elseif (
                    $cifra > 20 && (
                    $okon == 2 || $okon == 3 || $okon == 4
                    )
            ) {
                return 'а';
            } elseif (
                    $cifra > 10
            ) {
                return 'ов';
            } elseif (
                    $cifra == 2 || $cifra == 3 || $cifra == 4
            ) {
                return 'а';
            } elseif (
                    $okon == 0 || $okon == 1 || $okon == 5 || $okon == 6 || $okon == 7 || $okon == 8 || $okon == 9
            ) {
                return 'ов';
            } elseif (
                    $okon == 2 || $okon == 3 || $okon == 3
            ) {
                return 'а';
            }
        }
    }

    /**
     * конвертируем csv в массив
     * @param string $str
     * @param type $separator
     * @param type $quote
     * @return array
     */
    public static function f_csv($str, $separator = ';', $quote = '"') {

        // Убираем символ возврата каретки
        $str = trim(str_replace("\r", '', $str)) . "\n";

        $parsed = Array();    // Массив всех строк
        $i = 0;               // Текущая позиция в файле
        $quote_flag = false;  // Флаг кавычки
        $line = Array();      // Массив данных одной строки
        $varr = '';           // Текущее значение

        while ($i <= strlen($str)) {
            // Окончание значения поля
            if ($str[$i] == $separator && !$quote_flag) {
                $varr = str_replace("\n", "\r\n", $varr);
                $line[] = $varr;
                $varr = '';
            }
            // Окончание строки
            elseif ($str[$i] == "\n" && !$quote_flag) {
                $varr = str_replace("\n", "\r\n", $varr);
                $line[] = $varr;
                $varr = '';
                $parsed[] = $line;
                $line = Array();
            }
            // Начало строки с кавычкой
            elseif ($str[$i] == $quote && !$quote_flag) {
                $quote_flag = true;
            }
            // Кавычка в строке с кавычкой
            elseif ($str[$i] == $quote && $str[($i + 1)] == $quote && $quote_flag) {
                $varr .= $str[$i];
                $i++;
            }
            // Конец строки с кавычкой
            elseif ($str[$i] == $quote && $str[($i + 1)] != $quote && $quote_flag) {
                $quote_flag = false;
            } else {
                $varr .= $str[$i];
            }
            $i++;
        }

        return $parsed;
    }

    /**
     * Возвращает сумму прописью
     * @author runcore
     * @uses morph(...)
     */
    public static function num2str($num) {
        $nul = 'ноль';
        $ten = array(
            array('', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
            array('', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
        );
        $a20 = array('десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать');
        $tens = array(2 => 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто');
        $hundred = array('', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');
        $unit = array(// Units
            array('копейка', 'копейки', 'копеек', 1),
            array('рубль', 'рубля', 'рублей', 0),
            array('тысяча', 'тысячи', 'тысяч', 1),
            array('миллион', 'миллиона', 'миллионов', 0),
            array('миллиард', 'милиарда', 'миллиардов', 0),
        );
        //
        list($rub, $kop) = explode('.', sprintf("%015.2f", floatval($num)));
        $out = array();
        if (intval($rub) > 0) {
            foreach (str_split($rub, 3) as $uk => $v) { // by 3 symbols
                if (!intval($v))
                    continue;
                $uk = sizeof($unit) - $uk - 1; // unit key
                $gender = $unit[$uk][3];
                list($i1, $i2, $i3) = array_map('intval', str_split($v, 1));
                // mega-logic
                $out[] = $hundred[$i1]; # 1xx-9xx
                if ($i2 > 1)
                    $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3];# 20-99
                else
                    $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3];# 10-19 | 1-9
                // units without rub & kop
                if ($uk > 1)
                    $out[] = \f\morph($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);
            } //foreach
        } else
            $out[] = $nul;
        $out[] = \f\morph(intval($rub), $unit[1][0], $unit[1][1], $unit[1][2]); // rub
        $out[] = $kop . ' ' . \f\morph($kop, $unit[0][0], $unit[0][1], $unit[0][2]); // kop
        return trim(preg_replace('/ {2,}/', ' ', join(' ', $out)));
    }

    /**
     * Склоняем словоформу
     * @ author runcore
     */
    public static function morph($n, $f1, $f2, $f5) {
        $n = abs(intval($n)) % 100;
        if ($n > 10 && $n < 20)
            return $f5;
        $n = $n % 10;
        if ($n > 1 && $n < 5)
            return $f2;
        if ($n == 1)
            return $f1;
        return $f5;
    }

}
