<?php

namespace Nyos;

if (!defined('IN_NYOS_PROJECT'))
    die('<center><br><br><br><br><p>Сработала защита <b>c.NYOS</b> от злостных розовых хакеров.</p>
    <a href="http://www.uralweb.info" target="_blank">Создание, дизайн, вёрстка и программирование сайтов.</a><br />
    <a href="http://www.nyos.ru" target="_blank">Только отдельные услуги: Дизайн, вёрстка и программирование сайтов.</a>');

class Nyos {

//    public static $logs = '';
    public static $menu = false;
    public static $a_menu = [];
//    public static $folder = '';
//    public static $connecttype = FALSE; // CURL / SOCKET / NONE / FALSE
    public static $folder_now = null;
    public static $folder_all = null;

    /**
     * строка путь до файла кеша меню
     * @var type 
     */
    public static $cash_file_menu = null;

    /**
     * получаем папку сайта по домену (если не указали домен, то текущий домен)
     * @param type $db
     * @param string $domain0
     * @return string или null
     * @throws \NyosEx
     */
    public static function getFolder($db, string $domain0 = null) {

// global $status;

        if ($domain0 === null) {
            $now = true;
            $domain = str_replace('www.', '', strtolower($_SERVER['HTTP_HOST']));
        } else {
            $now = false;
            $domain = str_replace('www.', '', strtolower($domain0));
        }

        if (isset(self::$folder_all[$domain]))
            return self::$folder_all[$domain];

        if( isset(self::$folder_now) && self::$folder_now !== null && self::$folder_now == '' )
        return self::$folder_all[$domain] = '';
        
        try {

//            $stmt = $db->prepare('SELECT * FROM 2domain WHERE domain =:domain LIMIT 1;');
//            $stmt->bindValue(':domain', $domain, PDO::PARAM_STR);
//            $stmt->execute();

            $ff = $db->prepare('SELECT folder FROM `2domain` WHERE domain = :domain LIMIT 1;');
            $ff->execute( array( ':domain' => $domain ) );

            if ($f = $ff->fetch()) {

                if ($now === true)
                    self::$folder_now = $f['folder'];

                self::$folder_all[$domain] = $f['folder'];
                return isset($f['folder']{1}) ? $f['folder'] : null;
            } else {

                $ff = $db->prepare('INSERT INTO  `2domain` (domain) VALUES (?)');
                $ff->execute([$domain]);
                unset($ff);
            }
        }
        //
        catch (\PDOException $ex ) {
            
            echo '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
            . PHP_EOL . $ex->getMessage() . ' #' . $ex->getCode()
            . PHP_EOL . $ex->getFile() . ' #' . $ex->getLine()
            . PHP_EOL . $ex->getTraceAsString()
            . '</pre>';
            
// не найдена таблица, создаём значит её
            if (strpos($ex->getMessage(), 'no such table')) {

// echo '<Br/>ошибка DB:' . $e->getMessage();
                $db->exec('CREATE TABLE `2domain` ( ' .
                        ' `domain` varchar(150) NOT NULL, ' .
                        ' `folder` varchar(150) DEFAULT NULL ' .
                        ' );');

                $ff = $db->prepare('INSERT INTO  `2domain` (domain) VALUES (?)');
                $ff->execute([$domain]);
                unset($ff);
                
            } else {

                throw new \NyosEx('непонятная ошибка DB (выбираем папку по домену): ' . $ex->getMessage());
                
            }
        }
    }

    public static function defineVars($folder = null, array $cfg_mod_array = []) {

        global $vv;

        //\f\pa($cfg_mod_array);

        if (!defined('IN_NYOS_PROJECT'))
            define('IN_NYOS_PROJECT', TRUE);

        if (!defined('DS'))
            define('DS', DIRECTORY_SEPARATOR);

        $vv['domain'] = str_replace("www.", '', mb_strtolower($_SERVER['HTTP_HOST']));
        
        if ( $folder === null & isset($vv['folder']{1}) )
            $folder = $vv['folder'];
        
        if (!defined('domain'))
            define('domain', $vv['domain']);

        if ($folder === null && is_dir($_SERVER['DOCUMENT_ROOT'] . DS . 'site' . DS)) {
            $site_dir = DS . 'site' . DS;
        } elseif (is_dir($_SERVER['DOCUMENT_ROOT'] . DS . 'sites' . DS . $folder . DS)) {
            $site_dir = DS . 'sites' . DS . $folder . DS;
        }

        if (!isset($site_dir{1}))
            throw new \Exception('Не найдена папка с сайтом');

        /**
         * /папка сайта/
         */
        define('dir_site', $site_dir);

        /**
         * корень сайта/папка сайта/
         */
        define('dir_serv_site', $_SERVER['DOCUMENT_ROOT'] . dir_site);

        /**
         * корень сайта/папка сайта/модули/
         */
        define('dir_serv_module', $_SERVER['DOCUMENT_ROOT'] . DS .'module'.DS );

        /**
         * заглушка чтобы закрыть предыдущие версии, в новых используйте
         * dir_serv_site
         */
        define('DirSite', $_SERVER['DOCUMENT_ROOT'] . dir_site);

        /**
         * ссылка на папку дидрайва
         * корень сайта / дидрайв /
         */
        define('DirDidrive', $_SERVER['DOCUMENT_ROOT'] . DS . 'didrive' . DS);

        /**
         * ссылка на папку глобальных модулей дидрайва
         * корень сайта / дидрайв / модули /
         */
        define('DirDidriveModule', $_SERVER['DOCUMENT_ROOT'] . DS . 'didrive' . DS . 'module' . DS);

// формируем \Nyos\Nyos::$menu         
        self::getMenu();


        
        
        if (isset($_GET['level']) && isset(\Nyos\Nyos::$menu[$_GET['level']]) ) {
            $vv['level'] = $_GET['level'];
            $vv['now_level'] = \Nyos\Nyos::$menu[$vv['level']];
            $vv['now_level']['cfg.level'] = $vv['level'];
        } else if (isset($_GET['level_di']) && isset(\Nyos\Nyos::$menu['di'][$_GET['level_di']]) ) {
            $vv['level'] = $_GET['level_di'];
            $vv['now_level'] = \Nyos\Nyos::$menu['di'][$vv['level']];
            $vv['now_level']['cfg.level'] = $vv['level'];
        } else {
            $vv['level'] = '000.index';
            $vv['now_level'] = \Nyos\Nyos::$menu[$vv['level']];
            $vv['now_level']['cfg.level'] = $vv['level'];
        }

        if (!isset($vv['now_level']['cfg.level']))
            throw new \Exception('Нет важных данных в данных о текущем модуле');

        //\f\pa($vv['now_level']);

        /**
         * корень сайта/модули/текущий модуль/
         */
        define('dir_serv_mod', $_SERVER['DOCUMENT_ROOT'] . DS . 'module' . DS . $vv['now_level']['type'] . DS);
        /**
         * /модули/текущий модуль/
         */
        define('dir_mod', $_SERVER['DOCUMENT_ROOT'] . DS . 'module' . DS . $vv['now_level']['type'] . DS);

        /**
         * /модули/текущий модуль/версия модуля/
         */
        define('dir_mod_ver', DS . 'module' . DS . $vv['now_level']['type'] . DS . $vv['now_level']['version'] . DS);
        /**
         * корень сайта/модули/текущий модуль/версия модуля/
         */
        define('dir_serv_mod_ver', $_SERVER['DOCUMENT_ROOT'] . dir_mod_ver);

        /**
         * корень сайта/модули/текущий модуль/версия модуля/didrive/
         */
        define('dir_serv_mod_ver_didr', $_SERVER['DOCUMENT_ROOT'] . dir_mod_ver.'didrive'.DS);
        /**
         * корень сайта/модули/текущий модуль/версия модуля/didrive/tpl/
         */
        define('dir_mod_ver_didr_tpl', dir_mod_ver.'didrive'.DS.'t'.DS);
        /**
         * корень сайта/модули/текущий модуль/версия модуля/didrive/tpl/
         */
        define('dir_serv_mod_ver_didr_tpl', dir_serv_mod_ver_didr.DS.'t'.DS);

        /**
         * /модули/текущий модуль/версия модуля/tpl/
         */
        define('dir_mod_ver_tpl', DS . 'module' . DS . $vv['now_level']['type'] . DS . $vv['now_level']['version'] . DS . 'tpl' . DS);

        /**
         * корень сайта/модули/текущий модуль/версия модуля/tpl/
         */
        define('dir_serv_mod_ver_tpl', $_SERVER['DOCUMENT_ROOT'] . dir_mod_ver_tpl);













        /**
         * /папка сайта/download/
         */
        define('dir_site_sd', dir_site . 'download' . DS);
        $vv['sd'] = dir_site_sd;

        /**
         * корень сайта/папка сайта/download/
         */
        define('dir_serv_site_sd', $_SERVER['DOCUMENT_ROOT'] . dir_site_sd);

        /**
         * /папка сайта/template/
         */
        define('dir_site_tpl', dir_site . 'template' . DS);

        /**
         * корень сайта/папка сайта/template/
         */
        define('dir_serv_site_tpl', $_SERVER['DOCUMENT_ROOT'] . dir_site_tpl);

        /**
         * корень сайта/папка сайта/модули/текущий модуль/
         */
        define('dir_serv_site_mod', $_SERVER['DOCUMENT_ROOT'] . dir_site . 'module' . DS . $vv['now_level']['cfg.level'] . DS);
        /**
         * корень сайта/папка сайта/модули/текущий модуль/tpl/
         */
        define('dir_serv_site_mod_tpl', $_SERVER['DOCUMENT_ROOT'] . dir_site . 'module' . DS . $vv['now_level']['cfg.level'] . DS . 'tpl' . DS);

        self::$cash_file_menu = dir_serv_site . domain . '.cash.mnu.json';
        


        /**
         * /модули/текущий модуль/версия/дидрайв/
         */
        define('dir_mod_didr', DS . 'module' . DS . $vv['now_level']['type'] . DS . $vv['now_level']['version'] . DS . 'didrive' . DS);
        /**
         * корень сайта/модули/текущий модуль/версия/дидрайв/
         */
        define('dir_serv_mod_didr', $_SERVER['DOCUMENT_ROOT'] . dir_mod_didr);
        /**
         * корень сайта/модули/текущий модуль/версия/дидрайв/t/
         */
        define('dir_serv_mod_didr_tpl', dir_serv_mod_didr . 't' . DS);
    }

    /**
     * формируем менюшку сайта или достаём из файла данных
     * @return type
     */
    public static function getSiteModule() {

        if (sizeof(self::$a_menu) > 0)
            return self::$a_menu;

        //\f\pa(DirSite);
        $h = scandir(DirSite . '/module/');

        foreach ($h as $k => $v) {
            if (isset($v{2})) {
                $file_cfg = DirSite . 'module' . DS . $v . DS . 'cfg.ini';
                if (file_exists($file_cfg)) {
                    $a = parse_ini_file($file_cfg, true);
                    if (isset($a['type']{0}) && isset($a['version']{0})) {
                        $a['cfg.level'] = $v;
                        self::$a_menu[$v] = $a;
                    }
                }
            }
        }

        //echo DirSite;
        $h = scandir(DirDidriveModule);

        foreach ($h as $k => $v) {
            if (isset($v{2})) {
                $file_cfg = DirDidriveModule . $v . DS . 'cfg.ini';
                if (file_exists($file_cfg)) {
                    $a = parse_ini_file($file_cfg, true);
                    if (isset($a['type']{0}) && isset($a['version']{0})) {
                        $a['cfg.level'] = $v;
                        self::$a_menu['di'][$v] = $a;
                    }
                }
            }
        }

        return self::$a_menu;
    }

    /**
     * создаём секрет из строчки
     * @param type $text
     * что шифруем
     * @return type
     * на выходе секрет
     */
    public static function creatSecret($text) {
        return md5('wwdv' . $text . date('ymd', $_SERVER['REQUEST_TIME']));
    }

    /**
     * проверяем секрет 
     * @param string $secret
     * секрет
     * @param string $text
     * что шифровали
     * @return boolean
     */
    public static function checkSecret(string $secret, string $text) {
        if ($secret == md5('wwdv' . $text . date('ymd', $_SERVER['REQUEST_TIME']))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * формируем менюшку сайта или достаём из файла данных
     * @param string $folder
     * @return type
     */
    public static function getMenu(string $folder = null, string $domain = null) {

        //echo dir_serv_site;

        if (!defined('dir_serv_site'))
            throw new \Exception('не определена папка сайта при получени менюшки');

        // $file_cash = DirSite . 'module' . DS . '_cash_mnu.' . domain . '.cfg.php';
        $enable_cash = file_exists(dir_serv_site . 'nocreat_cash_module') ? false : true;

// если есть кеш и пользоваться им можно        
        if ($enable_cash === true && self::$cash_file_menu !== null && file_exists(self::$cash_file_menu)) {
            self::$menu = json_decode(file_get_contents(self::$cash_file_menu), true);
        }

// если формируем менюшку заново
        else {

            self::$menu = [];
            $rf = scandir(dir_serv_site . 'module/');

            foreach ($rf as $k => $v) {

                if (isset($v{0}) && $v != '.' && $v != '..' && file_exists(dir_serv_site . 'module' . DS . $v . DS . 'cfg.ini')) {

                    try {
                        self::$menu[$v] = parse_ini_file(dir_serv_site . 'module' . DS . $v . DS . 'cfg.ini', true);
                        self::$menu[$v]['cfg.level'] = $v;
                    }
                    //
                    catch (\Exception $ex) {
                        self::$menu[$v] = array(
                            'error' => $e->getMessage(),
                            'code' => $e->getCode(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        );
                    }
                }
            }

            //echo DirSite;
            $h = scandir(DirDidriveModule);

            foreach ($h as $k => $v) {
                if (isset($v{2})) {
                    $file_cfg = DirDidriveModule . $v . DS . 'cfg.ini';
                    if (file_exists($file_cfg)) {
                        $a = parse_ini_file($file_cfg, true);
                        if (isset($a['type']{0}) && isset($a['version']{0})) {
                            $a['cfg.level'] = $v;
                            self::$menu['di'][$v] = $a;
                        }
                    }
                }
            }

            if ($enable_cash === true && isset(self::$cash_file_menu{5}))
                file_put_contents(self::$cash_file_menu, json_encode(self::$menu));
        }
    }

}

class Nyos_old {

// $sql = $db->sql_query('SELECT * FROM `2domain` WHERE `domain` = \'' . addslashes($domain) . '\' LIMIT 1;');
// echo $status;
// если есть результат у запроса
//        if ($db->sql_numrows($sql) == 1) {
//
//            $e = $db->sql_fr($sql);
//
//            if ($now === true) {
//                self::$folder_now = self::$folder_all[$e['domain']] = $e['folder'];
//            } else {
//                self::$folder_all[$e['domain']] = $e['folder'];
//            }
//
//            return $e['folder'];
//        }
// если нет результата у запроса
//        else {
//            return false;
//        }
// старт
//function nyos(){}
//if ( $nyos -> isDomainAvailible('http://www.ruseller.com'))
//{
//echo "Работает и готов отвечать на запросы!";
//}
//else
//{
//echo "Ой, сайт не доступен.";
//}
// проверяем есть ил инет домен
    public static function domain($db, $domain) {
//global $status;
//$status = '';

        $d = str_replace('www.', '', strtolower($domain));
        $a = $db->sql_query("SELECT *
            FROM `2domain`
            WHERE `domain`='" . addslashes($d) . "'
            LIMIT 1;");

        if ($db->sql_numrows($a) == 1) {
            return $db->sql_fr($a);
        } else {
            \f\db\db2_insert($db, '2domain', array('domain' => $d), 'yes');
            return array('domain' => $d, 'new' => true);
        }

//echo $status;
    }

    /**
     * проверяем оплачен хостинг или нет
     * @param type $db
     * @param type $folder
     * @param type $domain
     * @return type
     */
    public static function hosting_pay($db = false, $folder = false, $domain = false) {

// global $status;
// $status = '';

        if ($domain === false)
            $domain = $_SERVER['HTTP_HOST'];

        $domain = str_replace('www.', '', strtolower($domain));

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/9.site/' . $folder . '/hosting_end')) {
            if (( filemtime($_SERVER['DOCUMENT_ROOT'] . '/9.site/' . $folder . '/hosting_end') + 3600 * 23 * 7 ) < $_SERVER['REQUEST_TIME']) {
                unlink($_SERVER['DOCUMENT_ROOT'] . '/9.site/' . $folder . '/hosting_end');
            } else {
                return file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/9.site/' . $folder . '/hosting_end');
            }
        } elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . '/9.site/' . $folder . '/hosting_ok')) {
            if (( filemtime($_SERVER['DOCUMENT_ROOT'] . '/9.site/' . $folder . '/hosting_ok') + 3600 * 23 * 7 ) < $_SERVER['REQUEST_TIME']) {
                unlink($_SERVER['DOCUMENT_ROOT'] . '/9.site/' . $folder . '/hosting_ok');
            } else {
                return true;
            }
        }


        if ($db !== false) {
            $sql = $db->sql_query("SELECT summa, folder
                FROM
                    `2hosting_reg`
                WHERE
                    `folder`='" . addslashes($folder) . "'
                    AND `oplachen_do` < curdate()
                LIMIT 1;");

// если дата оплаты меньше текущей
            if ($db->sql_numrows($sql) == 1) {
                $wr = $db->sql_fr($a);

                if (is_dir($_SERVER['DOCUMENT_ROOT'] . '/9.site/' . $folder)) {
                    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/9.site/' . $folder . '/hosting_end', $wr['summa']);
                    return $wr['summa'];
                }
            }
// если оплачено дальше текущей даты
            else {
                if (is_dir($_SERVER['DOCUMENT_ROOT'] . '/9.site/' . $folder)) {
                    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/9.site/' . $folder . '/hosting_ok', '');
                    return true;
                }
            }
        }
    }

    public static function creat_menu($folder) {

        if (self::$menu === false) {
// self::get_menu( $folder );
            self::get_menu($folder);
        }

//echo '<pre>'; print_r(self::menu); echo '</pre>';

        $out = array();

        foreach (self::$menu as $k => $v) {
            for ($e = 1; $e <= 10; $e ++) {

                if (!isset($out[$e]))
                    $out[$e] = array();

                if (isset($v['in-menu-' . $e]) && $v['in-menu-' . $e] == 'da') {
                    $out[$e][$k] = $v;
                }
            }
        }

        return $out;
    }

    /**
     * очищаем кеши
     * @param type $folder
     */
    public static function clearCash($folder) {

        if (!defined('DS'))
            define('DS', DIRECTORY_SEPARATOR);

        $dir = $_SERVER['DOCUMENT_ROOT'] . DS . '9.site' . DS . $folder . DS;

        if (is_dir($dir)) {

            $list = scandir($dir . 'module');

            foreach ($list as $k => $v) {

// кеши конфигов менюшек
                if (strpos($v, '_cash_mnu.') !== false && strpos($v, '.cfg.php') !== false)
                    unlink($dir . 'module' . DS . $v);
            }
        }
    }

    /**
     * получаем список модулей
     * @param type $folder
     * @param type $access
     * didrive - дидрайв настройки
     * adm - админсмкие настройки сайта
     * @return string
     */
    public static function getModuleAdmin($folder = null, $access = 'didrive') {

        if (!defined('DS'))
            define('DS', DIRECTORY_SEPARATOR);

// echo '11111111-'.domain;

        if (isset($folder) && $folder !== null) {
            
        } elseif (isset(self::$folder)) {
            $folder = self::$folder;
        } else {
            return \f\end3('Ошибка в указании папки #' . __LINE__, false);
        }

        $cash_dir = $_SERVER['DOCUMENT_ROOT'] . DS . '9.site' . DS . $folder . DS . 'module' . DS;
        $refresh = file_exists(DirSite . 'nocreat_cash_module') ? true : false;
        $_file_cash = $cash_dir . '_cash_mnu.' . $access . '.' . domain . '.cfg.php';

//if (    strpos( $_SERVER['DOCUMENT_ROOT'], ':' ) === false )
//echo '<br/>'.__FILE__.'#'.__LINE__;
//if ( $refresh !== false )
//echo '<br/>'.__FILE__.'#'.__LINE__;
//if( file_exists($_file_cash)
//    && filectime( $_file_cash ) > $_SERVER['REQUEST_TIME']-3600*24*7 )
//echo '<br/>'.__FILE__.'#'.__LINE__;
// берём инфу из файла кеша
        if (1 == 2 && file_exists($_file_cash) && filectime($_file_cash) > $_SERVER['REQUEST_TIME'] - 3600 * 24 * 7 && $refresh === false && strpos($_SERVER['DOCUMENT_ROOT'], ':') === false) {
            $a_menu = unserialize(file_get_contents($_file_cash));
            return \f\end3('выбрали модули ' . $access . ' (кеш)', true, $a_menu);
        }
// формируем список своими силами
        else {

            $dir = $_SERVER['DOCUMENT_ROOT'] . DS . '0.site' . DS . $access . '.module' . DS;
            $rf = scandir($dir);

//echo '<pre>'; print_r( $rf ); echo '</pre>';

            $a_menu = array();

            foreach ($rf as $k => $v) {

                if (is_dir($dir . $v) && is_file($dir . $v . DS . 'cfg.ini')) {

                    $a_menu[$v] = parse_ini_file($dir . $v . DS . 'cfg.ini', true);
                    $a_menu[$v]['cfg.level'] = $v;

                    /*
                      if (isset($a_menu[$v]['cfg.name']) && $a_menu[$v]['cfg.name'] == 'from_book') {

                      if ($a_menu[$v]['type'] == 'book.txt' &&
                      $a_menu[$v]['version'] == '2' &&
                      is_file(DirSite . 'download' . DS . domain . DS . $v . '.fb2.arr')
                      ) {
                      $dtxt = unserialize(file_get_contents(DirSite . 'download' . DS . domain . DS . $v . '.fb2.arr'));
                      $a_menu[$v]['name'] = // $TitleN =
                      ( isset($dtxt['info']['autor']{1}) ? $dtxt['info']['autor'] . ': ' : '' ) .
                      ( isset($dtxt['info']['title']{1}) ? ' ' . $dtxt['info']['title'] : ')' );
                      }
                      }
                     */

//echo $v.' -=- '.__LINE__.'<br/>';
                }

//echo __LINE__.'<br/>';
            }
//echo DirSite.DS.'module'.DS.domain.'_cash.cfg.php-'.sizeof($a_menu);

            file_put_contents($_file_cash, serialize($a_menu));
//echo '<pre>'; print_r( $data ); echo '</pre>';
            return \f\end3('выбрали модули ' . $access . ' (реал тайм, не кеш)', true, $a_menu);
        }

        return \f\end3('Ошибка при выборке #' . __LINE__, false);
    }

    /**
     * создаём пароль
     * @param цифра $number сколько знаков в пароле
     * @param цифра $type ( 1 сложный, 2 простой)
     * @return строка
     */
    public static function creat_pass($number = 7, $type = 1) {
        $arr = array(
            'a', 'b', 'c', 'd', 'e', 'f',
            'g', 'h', 'i', 'j', 'k', 'l',
            'm', 'n', 'o', 'p', 'r', 's',
            't', 'u', 'v', 'x', 'y', 'z',
            'A', 'B', 'C', 'D', 'E', 'F',
            'G', 'H', 'I', 'J', 'K', 'L',
            'M', 'N', 'O', 'P', 'R', 'S',
            'T', 'U', 'V', 'X', 'Y', 'Z',
            '1', '2', '3', '4', '5', '6',
            '7', '8', '9', '0'
        );

        $arr2 = array(
            '.', ',',
            '(', ')',
            '[', ']',
            '!', '?',
            '&', '_',
            '%', '@'
        );

        $pass = '';

        for ($i = 1; $i < $number; $i++) {
            $index = rand(0, count($arr) - 1);
            $pass .= $arr[$index];
        }

        if ($type == 1) {
            $pass .= $arr2[rand(0, count($arr2) - 1)];
        } else {
            $pass .= rand(0, count($arr) - 1);
        }

        return $pass;
    }

// список данных
    public static function alist($db = false, $type = 'folder', $filtr1 = false, $filtr2 = false) {

        if ($type == 'modules') {

            $rr = self::alist($db, 'site'); // , str_replace( 'www.', '', strtolower($_SERVER['HTTP_HOST']) ) );
            $d = str_replace('www.', '', strtolower($_SERVER['HTTP_HOST']));

            if (isset($rr[$d]{2})) {

                self::$a_menu = array();
                $h = scandir($_SERVER['DOCUMENT_ROOT'] . '/9.site/' . $rr[$d] . '/module/');

                foreach ($h as $k => $v) {
                    if (isset($v{2}) && file_exists($_SERVER['DOCUMENT_ROOT'] . DS . '9.site' . DS . $rr[$d] . DS . 'module' . DS . $v . DS . 'cfg.ini')) {
                        self::$a_menu[$v] = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . DS . '9.site'
                                . DS . $rr[$d] . DS . 'module' . DS . $v . DS . 'cfg.ini', true);
                        self::$a_menu[$v]['cfg.level'] = $v;
                    }
                }

                return self::$a_menu;
            }
        } elseif ($type == 'site') {

//$status = '';
            $x = $db->sql_query("SELECT *
                    FROM
                        `2domain`
                    ORDER BY
                        `domain` ASC;");
//echo $status;

            while ($r = $db->sql_fr($x)) {
                if (isset($r['domain']{1})) {
                    $d[$r['domain']] = $r['folder'];

                    if ($r['domain'] == strtolower(str_replace('www.', '', $_SERVER['HTTP_HOST'])))
                        self::$folder = $r['folder'];
                }
            }

            return $d;
        }

        elseif ($type == 'hosting') {

//$status = '';
            $x = $db->sql_query("SELECT *
                    FROM
                        `2hosting_reg`
                    WHERE
                        `status` = 'job'
                    ORDER BY
                        `oplachen_do` ASC;");
//echo $status;

            $d = array();

            while ($r = $db->sql_fr($x)) {

// $startdate = date("Y-m-d 00:00:00");
                $st = strtotime($r['oplachen_do'] . ' 00:00:00');
                $st2 = strtotime(date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' + 1 month');

                /*
                  echo '-------<br/>';
                  echo $st.'<Br/>';
                  echo date( 'Y-m-d H:i:s', $st ).'<br/>';
                  echo $st2.'<Br/>';
                  echo date( 'Y-m-d H:i:s', $st2 ).'<br/>';
                  echo ( $st2 - $st ).'<Br/>';
                  echo $_SERVER['REQUEST_TIME'].'<Br/>';
                 */

                $r['td_st'] = ( $st2 > $st ) ? 'warning' : '';

                $d[$r['folder']] = $r;
            }

// echo '<pre>'; print_r( $d ); echo '<pre>';

            return $d;
        } elseif ($type == 'admins') {
//$status = '';
            $x = $db->sql_query("SELECT
                        *
                        ,`folder` as `f`
                    FROM
                        `0access`
                    ORDER BY
                        `folder` DESC,
                        `login` ASC
                        ;");
//echo $status;

            $v2 = array();

            while ($r = $db->sql_fr($x)) {
                $v2[$r['id']] = $r;
            }

            return $v2;
        }
//if( $type == 'folder' ){
        else {
            $o = scandir($_SERVER['DOCUMENT_ROOT'] . '/9.site');

            ksort($o);

            foreach ($o as $k => $v) {
                if (isset($v{2}) && is_dir($_SERVER['DOCUMENT_ROOT'] . '/9.site/' . $v)) {
                    $v2[$v] = 1;
                }
            }
        }

        return $v2;
    }

//Возвращает true, если домен доступен
    function isDomainAvailible($domain) {

//Проверка на правильность URL
        if (!filter_var($domain, FILTER_VALIDATE_URL)) {
            return false;
        }

//Инициализация curl
        $curlInit = curl_init($domain);
        curl_setopt($curlInit, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curlInit, CURLOPT_HEADER, true);
        curl_setopt($curlInit, CURLOPT_NOBODY, true);
        curl_setopt($curlInit, CURLOPT_RETURNTRANSFER, true);

//Получаем ответ
        $response = curl_exec($curlInit);

        curl_close($curlInit);

        if ($response)
            return true;

        return false;
    }

// Замена cURL для функции file_get_contents()
    function file_get_contents_curl($url) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Устанавливаем параметр, чтобы curl возвращал данные, вместо того, чтобы выводить их в браузер.
        curl_setopt($ch, CURLOPT_URL, $url);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

//Получаем последний статус Twitter
    function get_status($twitter_id, $hyperlinks = true) {

// Использовать функцию очень просто:
// echo get_status('catswhocode');

        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, "http://twitter.com/statuses/user_timeline/$twitter_id.xml?count=1");
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        $src = curl_exec($c);
        curl_close($c);
        preg_match('/<text>(.*)<\/text>/', $src, $m);
        $status = htmlentities($m[1]);
        if ($hyperlinks)
            $status = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]", '<a href="%5C%22%5C%5C0%5C%22">\\0</a>', $status);
        return($status);
    }

//Загружаем и сохраняем изображения со страницы с помощью cURL
//$i = 1;
//$l = 101;
//
//while ($i < $l) {
//    $html = get_data('http://somedomain.com/id/'.$i.'/');
//    getImages($html);
//    $i += 1;
//}

    function getImages($html) {
        $matches = array();
        $regex = '~http://somedomain.com/images/(.*?)\.jpg~i';
        preg_match_all($regex, $html, $matches);
        foreach ($matches[1] as $img) {
            saveImg($img);
        }
    }

    function saveImg($uri) {
        $data = $this->file_get_contents_curl($url);
        file_put_contents('photos/' . $name . '.jpg', $data);
    }

    /* Конвертируем валюту с помощью cURl и Google
     * Пересчет валюты достаточно простое дело, но курсы достаточно часто изменяются,
     * поэтому приходится использовать сервисы, подобные Google, для получения текущих
     * значений курса пересчета. Функция currency()
     * получает 3 параметра: исходная валюта, целевая валюта и сумма.
     */

    function currency($from_Currency, $to_Currency, $amount) {
        $amount = urlencode($amount);
        $from_Currency = urlencode($from_Currency);
        $to_Currency = urlencode($to_Currency);
        $url = "http://www.google.com/ig/calculator?hl=en&q=$amount$from_Currency=?$to_Currency";
        $ch = curl_init();
        $timeout = 0;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $rawdata = curl_exec($ch);
        curl_close($ch);
        $data = explode('"', $rawdata);
        $data = explode(' ', $data['3']);
        $var = $data['0'];
        return round($var, 2);
    }

// Получаем информацию о размере файла с помощью cURL
    function remote_filesize($url, $user = "", $pw = "") {
        ob_start();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);

        if (!empty($user) && !empty($pw)) {
            $headers = array('Authorization: Basic ' . base64_encode("$user:$pw"));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $ok = curl_exec($ch);
        curl_close($ch);
        $head = ob_get_contents();
        ob_end_clean();

        $regex = '/Content-Length:\s([0-9].+?)\s/';
        $count = preg_match($regex, $head, $matches);

        return isset($matches[1]) ? $matches[1] : "unknown";
    }

// Загрузка через FTP с помощью cURL
    function curl_post_file_on_ftp($puth_new_file_on_server, $ftp_login, $ftp_pass, $ftp_host, $ftp_puth_file) {

// Открываем файл
//$file = fopen("/path/to/file", "r");
        $file = fopen($puth_new_file_on_server, "r");

// URL содержит большую часть нужной информации
//$url = "ftp://username:password@mydomain.com:21/path/to/new/file";
        $url = 'ftp://' . $ftp_login . ':' . $ftp_pass . '@' . $ftp_host . ':21' . $ftp_puth_file;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// Устанавливаем опции
        curl_setopt($ch, CURLOPT_UPLOAD, 1);
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($puth_new_file_on_server));

// Устанавливаем режим ASCII (то есть - файл текстовой)
        curl_setopt($ch, CURLOPT_FTPASCII, 1);

        $output = curl_exec($ch);
        curl_close($ch);
    }

// добавление сайта в систему продажи ссылок
    function salelink_add_site($domain, $system, $user, $password, $dop = FALSE) {
        self::$logs .= 'start ' . __FUNCTION__ . '<br/>' .
                'дата = ' . $domain . '/' . $system . '/' . $user . '/' . $password . '/' . $dop . '<br/>';

        if ($system == 'mainlink') {

            if (!is_file(DirAll . 'class/nusoap-0.9.5/lib/nusoap.php'))
                die('неописуемая ситуация с файлом класса #' . __LINE__);

            require_once( DirAll . 'class/nusoap-0.9.5/lib/nusoap.php' );

            if (isset($dop{1}) && $dop == 'sys_IsAuthenticated') {
                $login = new nusoap_client("http://api.mainlink.ru/start.asmx?WSDL", true);
//$login->setUseCurl(1);
                $res = $login->call($dop);
//echo '<pre>'; print_r($res); echo '</pre>';
                return $res[$dop . 'Result'];
            } elseif (
// получение списка сайтов привязанных к пользователю
                    isset($dop{1}) && $dop == 'mlapi_GetSites'
            ) {
                $login = new nusoap_client("http://api.mainlink.ru/start.asmx?WSDL", true);

// если локально то не используем курл
//if( strpos($_SERVER['DOCUMENT_ROOT'], 'W:') !== false ){}
//else{ $login->setUseCurl(1); }
                $login->setUseCurl(0);

                $res = $login->call('sys_LogIn', array(
                    'Login' => $user,
                    'Password' => $password
                ));
                $cookies = $login->getCookies();

                $webmaster_url = "http://api.mainlink.ru/webmaster.asmx?WSDL";
                $wm = new nusoap_client($webmaster_url, true);
                $wm->setUseCurl(0);
                foreach ($cookies as $cookie) {
                    $wm->setCookie($cookie['name'], $cookie['value']);
                }
                return $wm->call($dop);
            } else {

                $login = new nusoap_client("http://api.mainlink.ru/start.asmx?WSDL", true);
// $advert = new nusoap_client("http://api.mainlink.ru/advert.asmx?WSDL", true);
// если локально то не используем курл
//if( strpos($_SERVER['DOCUMENT_ROOT'], 'W:') !== false ){}
//else{ $login->setUseCurl(1); }

                $login->setUseCurl(0);

                $res = $login->call('sys_LogIn', array(
                    'Login' => $user,
                    'Password' => $password
                ));

//echo '<hr>'.__LINE__.'<hr>
//    <h2>Запрос</h2><pre>' . htmlspecialchars($login->request, ENT_QUOTES) . '</pre>
//    <h2>Ответ</h2><pre>' . htmlspecialchars($login->response, ENT_QUOTES) . '</pre>
//    <hr>результат<pre>'; print_r($res); echo '</pre><hr>'.__LINE__.'<hr>';
// echo $balance = $login->call('sys_Balance', array());

                $cookies = $login->getCookies();

//echo '<hr><hr>';
//echo '<pre>'; print_r($cookies); echo '</pre>';
//echo '<hr><hr>';
//echo '<h2>Запрос</h2>';
//echo '<pre>' . htmlspecialchars($login->request, ENT_QUOTES) . '</pre>';
//echo '<h2>Ответ</h2>';
//echo '<pre>' . htmlspecialchars($login->response, ENT_QUOTES) . '</pre>';

                $webmaster_url = "http://api.mainlink.ru/webmaster.asmx?WSDL";
                $wm = new nusoap_client($webmaster_url, true);

                $wm->setUseCurl(0);

                foreach ($cookies as $cookie) {
                    $wm->setCookie($cookie['name'], $cookie['value']);
//SetCookie($cookie['name'], $cookie['value']);
                }

                $ddom = htmlspecialchars($domain);
                $res = $wm->call('mlapi_AddSite', array(
                    'Url' => $ddom, // ] url домена (без http://),
                    'Name' => 'site ' . $ddom, // название сайта,
                    'Description' => $ddom . ' sensation sites', // ] описание сайта,
// [CategoryID] каталог сайта (см. справочники),
                    'CategoryID' => 22, // ] каталог сайта (см. справочники),
                    'ScanSite' => TRUE // ] сканирование сайта при добавлении (true-сканировать, false-не сканировать)
                ));

//echo '<hr><hr>';
//echo '<pre>'; print_r($res); echo '</pre>';
//echo '<hr><hr>';

                echo '<h2>Запрос</h2>' .
                '<pre>' . htmlspecialchars($wm->request, ENT_QUOTES) . '</pre>' .
                '<h2>Ответ</h2>' .
                '<pre>' . htmlspecialchars($wm->response, ENT_QUOTES) . '</pre>';

                if (isset($res['mlapi_AddSiteResult']) && is_numeric($res['mlapi_AddSiteResult'])) {
                    if ($res['mlapi_AddSiteResult'] == 0) {
                        $err = 'неавторизован';
                    } elseif ($res['mlapi_AddSiteResult'] == -1) {
                        $err = 'некорректный урл домена';
                    } elseif ($res['mlapi_AddSiteResult'] == -2) {
                        $err = 'сайт с таким URL уже есть в базе';
                    } elseif ($res['mlapi_AddSiteResult'] == -3) {
                        $err = 'сайт недоступен';
                    } elseif ($res['mlapi_AddSiteResult'] == -4) {
                        $err = 'не добавлен (проблема с СУБД)';
                    } elseif ($res['mlapi_AddSiteResult'] == -5) {
                        $err = 'письмо не отправлено';
                    } elseif ($res['mlapi_AddSiteResult'] == -6) {
                        $err = 'код не найден (или секьюрный файл не найден в коде';
                    } else {
                        $err = 'добавлен сайт #' . $res['mlapi_AddSiteResult'] . ' ';
                    }
                } else {
                    $err = 'обработка ошибочна #' . __LINE__ . ' ';
                }

                return $err;
            }

            self::$logs .= 'end = ok<br/>';
            return true;
        }

        self::$logs .= 'end = error // так как не выбрана система<br/>';
        return false;
    }

    function get_external_type() {

        if (function_exists('curl_init'))
            return self::$connecttype = 'CURL';
        elseif (function_exists('fsockopen'))
            return self::$connecttype = 'SOCKET';
        else
            return self::$connecttype = 'NONE';
    }

    function get_external_html($host, $uri, $type = 'http') {
        /*
          return '<?xml version="1.0" encoding="utf-8" ?> <page> <domains><domain><emails><action-status/><email><name>info</name></email><found>1</found><total>1</total></emails><name>dol-lenina.ru</name><status>added</status><emails-max-count>1000</emails-max-count></domain></domains> </page>';
          //$nyos->get_external_html
         */

        /*
          return '<?xml version="1.0" encoding="utf-8"?> <page> <domains><domain><emails><action-status/><email><name>boss</name></email><email><name>support</name></email><email><name>sergey</name></email><found>3</found><total>3</total></emails><name>72sms.ru</name><status>added</status><emails-max-count>1000</emails-max-count></domain></domains> </page>';
         */

        if (self::$connecttype === FALSE)
            self::$get_external_type() . '<br/>';

        if (self::$connecttype == 'CURL') {


            if ($type == 'https') {

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($curl, CURLOPT_MAXREDIRS, 5);

//curl_setopt($curl, CURLOPT_POST, 1);
//curl_setopt($curl, CURLOPT_POST, 1);
//curl_setopt($curl, CURLOPT_POSTFIELDS, urlencode_array($postargs));

                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($curl, CURLOPT_URL, 'https://' . $host . '/' . $uri);

                $data = curl_exec($curl);
//$info = curl_getinfo($curl);
                curl_close($curl);

//return htmlspecialchars($data);
                return $data;
            }
        }
        if (self::$connecttype == 'SOCKET') {


            $fp = fsockopen($host, ( ($type == 'https') ? 443 : 80), $errno, $errstr, 30);
            if (!$fp) {
                echo "$errstr ($errno)
";
            } else {
                $query = "GET / HTTP/1.1
Host: " . $host . "
Connection: Close
User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)
" . $uri;

                fwrite($fp, $query);
                $page = '';

                while (!feof($fp)) {
                    $page .= fgets($fp, 4096);
                }

                fclose($fp);

                if (!empty($page))
                    return $page;
//echo '<pre>'.$page.'</pre>';
            }
        }
        if (self::$connecttype == 'NONE') {
            
        }
    }

    /*
      //$icquin = "197829943";
      function get_status_icq($uin)
      {

      if (!is_numeric($uin)) return FALSE;

      $fp = fsockopen('status.icq.com', 80, $errno, $errstr, 8);

      if (!$fp)
      {
      return "N/A";
      }
      else
      {
      $request = "HEAD /online.gif?icq=$uin HTTP/1.0\r\n"
      ."Host: web.icq.com\r\n"
      ."Connection: close\r\n\r\n";
      fputs($fp, $request);

      do
      {
      $response = fgets($fp, 1024);
      }
      while (!feof($fp) && !stristr($response, 'Location'));

      fclose($fp);

      if( strstr($response, 'online1') ) return 'Online';
      if( strstr($response, 'online0') ) return 'Offline';
      if( strstr($response, 'online2') ) return 'N/A';
      // N/A means, this User set the Option, his Online
      // Status cannot be shown over the Internet

      return FALSE;
      }
      }
      //echo GetICQ($icquin);
     */

    /**
     * получаем к какой папке привязан домен
     * @param класс $db
     * @param строка $domain
     */
    public static function getFolder0($db, $domain = null) {

// global $status;

        if ($domain === null) {
            $now = true;
            $domain = str_replace('www.', '', strtolower($_SERVER['HTTP_HOST']));
        } else {
            $now = false;
        }

        if (isset(self::$folder_all[$domain]))
            return self::$folder_all[$domain];

        $sql = $db->sql_query('SELECT * FROM `2domain` WHERE `domain` = \'' . addslashes($domain) . '\' LIMIT 1;');

// echo $status;
// если есть результат у запроса
        if ($db->sql_numrows($sql) == 1) {
            $e = $db->sql_fr($sql);

            if ($now === true) {
                self::$folder_now = $e['folder'];
                self::$folder_all[$e['domain']] = $e['folder'];
            } else {
                self::$folder_all[$e['domain']] = $e['folder'];
            }

            return $e['folder'];
        }
// если нет результата у запроса
        else {
            return false;
        }
    }

    /**
     * проверяем включён режим кеша одной страницы или нет
     * @param string $folder
     * @return type
     */
    public static function checkOnOffCashSiteOnePage(string $folder) {

        return file_exists($_SERVER['DOCUMENT_ROOT'] . '/9.site/' . $folder . '/site_is_one_page') ? true : false;
    }

    /**
     * запись страницы в кеш
     * @param string $html
     */
    public static function creatCashSiteIsOnePage(string $html) {

        if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/0.cash'))
            mkdir($_SERVER['DOCUMENT_ROOT'] . '/0.cash', 0755);

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/0.cash/' . strtolower($_SERVER['HTTP_HOST']) . '.one_page.cash24', $html);
    }

    /**
     * чистим кеш если он есть
     * @param type $type
     * time4 - если файлу кеша больше 4 часов, то удаляем
     */
    public static function clearCashSiteIsOnePage($type = null) {

        $file = $_SERVER['DOCUMENT_ROOT'] . '/0.cash/' . strtolower($_SERVER['HTTP_HOST']) . '.one_page.cash24';

        if (file_exists($file) &&
                (
                ( $type == 'time4' && $_SERVER['REQUEST_TIME'] - 3600 * 4 > filectime($file) ) || $type === null
                )
        ) {
            unlink($file);
        }
    }

    /**
     * показ данных из кеша страницы по домену
     * @return boolean
     */
    public static function showCashSiteIsOnePage() {

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/0.cash/' . strtolower($_SERVER['HTTP_HOST']) . '.one_page.cash24')) {
            die(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/0.cash/' . strtolower($_SERVER['HTTP_HOST']) . '.one_page.cash24'));
        }
    }

    public static function enter($db, $host, $login, $pass) {

//        $ww = $db->sql_query('SELECT *
//            FROM
//                `0access`
//                ,`2domain`
//            WHERE
//                `2domain`.`domain` = \'' . addslashes($host) . '\'
//                AND `2domain`.`folder` = `0access`.`folder`
//                AND (
//                    `0access`.`login` = \'' . addslashes($login) . '\'
//                    OR
//                    `0access`.`mail` = \'' . addslashes($login) . '\'
//                    )
//                AND `0access`.`pass` = \'' . md5($pass) . '\'
//            LIMIT 1
//            ;');

        $ww = $db->sql_query('SELECT a.*
            FROM
                0access a
                ,2domain d
            WHERE
                d.`domain` = \'' . addslashes($host) . '\'
                AND 
                d.`folder` = a.`folder`
                AND (
                    a.`login` = \'' . addslashes($login) . '\'
                    OR
                    a.`mail` = \'' . addslashes($login) . '\'
                    )
                AND a.`pass` = \'' . md5($pass) . '\'
            LIMIT 1
            ;');

        if ($db->sql_numrows($ww) == 1) {
            $_SESSION['now_user_di'] = $db->sql_fr($ww);

            require_once $_SERVER['DOCUMENT_ROOT'] . '/0.site/exe/lk/class.php';
            $da = \Nyos\mod\lk::getUserOptions($db, $_SESSION['now_user_di']['id']);
            \f\pa($da);

            die(__LINE__);

            return true;
        } else {
            return false;
        }
    }

}

// $nyos = new nyos();