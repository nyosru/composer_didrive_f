<?php

namespace f\db;

// use f as f;

if (!defined('IN_NYOS_PROJECT'))
    die('Сработала защита <b>функций MySQL</b> от злостных розовых хакеров.' .
            '<br>Приготовтесь к DOS атаке (6 поколения на ip-' . $_SERVER["REMOTE_ADDR"] . ') в течении 30 минут... .');

/**
 * 
 * @global type $status
 * @param класс $db
 * @param строка $sql
 * запрос
 * @param строка $key
 * указание поля для ключа в массиве
 * | 2 и есть поля id value То формируем одномерный массив
 * | 3 то вывод только value
 * | 4 вывод 1 массива ( LIMIT 1 )
 * @return type
 */
function getSql($db, $sql, $key = 'id') {

//    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
//        global $status;
//        $status .= '<fieldset class="status" ><legend>' . __CLASS__ . ' #' . __LINE__ . ' + ' . __FUNCTION__ . '</legend>';
//    }

    $res = array();

    $ff = $db->prepare($sql);
    $ff->execute();

    if ($r = $ff->fetch()) {

//    $sql2 = $db->sql_query($sql);
//    while ($r = $db->sql_fr($sql2)) {

        if ($key == 4) {
            $res = $r;
        } elseif ($key == 2 && isset($r['id']) && isset($r['value'])) {
            $res[$r['id']] = $r['value'];
        } elseif ($key == 3 && isset($r['value'])) {
            $res = $r['value'];
        } elseif (isset($key{1}) && isset($r[$key])) {
            $res[$r[$key]] = $r;
        } else {
            $res[] = $r;
        }
    }

//    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
//        $status .= '<span class="bot_line">#' . __LINE__ . '</span></fieldset>';
//    }

    return $res;
}

/**
 * добавление записи в БД (PDO)
 * @global type $status
 * @param классБД $db2
 * @param строка $table
 * таблица
 * @param массив $var_array
 * данные
 * @param правдаложь $slash
 * true\false
 * @param строка $return
 * last_id - возврат добавленной строки
 * @return boolean
 * @throws \NyosEx
 */
function db2_insert($db, string $table, $var_array, $slash = false, $return = null, $skip_null = true) {

    $polya = \f\db\pole_list($db, $table);
    // \f\pa($polya);
    // var_dump($polya);

    if (empty($polya)) {

        $all_data = true;

        //if ( $polya === false || sizeof($polya) == 0 ) {
        // echo '<br/>'.__FILE__.' #'.__LINE__;
        throw new \PDOException('no list polya in table ' . $table);
    } else {
        $all_data = false;
    }
    // echo '<br/>'.__FILE__.' #'.__LINE__;

    $all_val = $all_key = '';

    foreach ($var_array as $key => $v) {

        if ($all_data = false && !isset($polya[$key]))
            continue;

        if ($skip_null === true && ( strtolower($v) == 'null' || $v == '' ))
            continue;

        $all_val .= isset($all_val{1}) ? ',' : '';
        $all_key .= isset($all_key{1}) ? ',' : '';

        //if( strlen($val)<0 || $val == '')
        if (strtolower($v) == 'null' || $v == '') {
            $all_val .= ' NULL';
            // $debug['sql_struktura'][$key] = $val;
//        } elseif ($v == 'NOW()' || $v == 'NOW') {
//            $all_val .= $_SERVER['REQUEST_TIME'];
        } else {
            $all_val .= ( $slash === false ) ? '\'' . $var_array[$key] . '\'' : '\'' . addslashes($var_array[$key]) . '\'';
        }

        $all_key .= ' `' . $key . '`';
    }

//    echo '<br/>';
//    echo '<br/>key:'.$all_key;
//    echo '<br/>val:'.$all_val;
//    echo '<br/>';
//    echo '<br/>';

    if (!isset($all_key{1}) || !isset($all_val{1})) {

        throw new \NyosEx('Ошибка при добавлении записи в бд, неопознаны данные');
    }

    try {

        $s = 'INSERT INTO `' . $table . '` ( ' . $all_key . ' ) VALUES ( ' . $all_val . ' );';
//        echo '<Br/><Br/>'
//        . $s
//        . '<Br/><Br/>';
        $s2 = $db->prepare($s);
        $s2->execute();

        // echo " добавили запись ";
        if ($return == 'last_id') {
            return $db->lastInsertId();
        } else {
            return true;
        }
    } catch (\PDOException $ex) {

        throw new \NyosEx('Ошибка при добавлении записи в бд ' . $ex->getMessage());
    }
}

/**
 * получаем список столбцов PDO
 * @param type $db
 * @param string $table
 * @return boolean
 */
function pole_list($db, string $table) {

    try {

        $s = $db->query('SELECT sql FROM `sqlite_master` WHERE `name` = \'' . addslashes($table) . '\' LIMIT 1 ');

        if ($r = $s->fetch()) {
            //echo $r['sql'].'<br/>';
            preg_match_all('/\`(.+?)\`/', $r['sql'], $matches);
            //echo '<pre>'; print_r($matches); echo '</pre>';

            $re = [];

            foreach ($matches[1] as $k => $v) {
                if ($v == $table)
                    continue;

                $re[$v] = true;
            }

            return $re;
        }
        else {
            return false;
        }
    } catch (\PDOException $ex) {
        return false;
    }
}

function db2_insert_old1904007($db2, $table, $var_array, $slash = false, $return = null) {

    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
        GLOBAL $status;

//GLOBAL $status, $debug;
// вряме на выполнение

        if (function_exists('timer')) {
            $timer_rand = rand(5654, 987987);
            timer('db_insert' . $timer_rand, 'старт');
        }

        $status .= '<table><tr><td width="15" bgcolor="#DDDD33" align="center" nowrap style="direction: ltr; writing-mode:  tb-rl;"><b>DB_insert</b></td><td bgcolor="#EFedDE"> <b>функция ' . __FUNCTION__ . '</b> <br>'
                . 'Используется таблица <strong>' . $table . '</strong>.<br>';
    }
//reset ($var_array);
// перебираем массив с переменными и компилируем переменные
    $AllFieldName = pole2_list($db2, $table);

    $all_val = $all_key = '';

    //foreach ($var_array as $key => $val) {
    foreach ($AllFieldName as $key => $val) {

        if (!isset($var_array[$key])) {

            if (isset($_SESSION['status1']) && $_SESSION['status1'] === true)
                $status .= '<br/>поле |' . ( isset($key) ? $key : '--' ) . '| <strike>заполнено</strike> [' . ( isset($var_array[$key]) ? $var_array[$key] : '--' ) . ']';

            continue;
        }

// echo 'Переменная <strong>' . $key . '</strong> = <u>' . $val . '</u><br>';
        if (isset($_SESSION['status1']) && $_SESSION['status1'] === true)
            $status .= '<br/>Переменная <strong>' . $key . '</strong> = <u>' . $var_array[$key] . '</u>';

        $all_val .= isset($all_val{1}) ? ',' : '';
        $all_key .= isset($all_key{1}) ? ',' : '';

        //if( strlen($val)<0 || $val == '')
        if ($var_array[$key] == 'null' || $var_array[$key] == 'NULL') {
            $all_val .= ' NULL';
            // $debug['sql_struktura'][$key] = $val;
        } elseif ($var_array[$key] == 'NOW()' || $var_array[$key] == 'NOW') {
            $all_val .= ' NOW()';
        } else {
            $all_val .= ($slash == 'no' || $slash === false ) ? '\'' . $var_array[$key] . '\'' : '\'' . addslashes($var_array[$key]) . '\'';
        }

        $all_key .= ' `' . $key . '`';
    }

    if (isset($all_key{1}) && isset($all_val{1}))
        $res = $db2->sql_query('INSERT INTO `' . $table . '` ( ' . $all_key . ' ) VALUES ( ' . $all_val . ' );');

// вряме на выполнение
    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true && function_exists('timer')) {
        timer('db_insert' . $timer_rand, 'конец');
        timer('db_insert' . $timer_rand, 'итог');
    }

    if ($return == 'last_id') {

        // $db2->db_connect_id->insert_id;

        if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
            $status .= '<br/>добавлен id: ' . $db2->db_connect_id->insert_id . '</td></tr></table> '
                    . '<span class="bot_line">#' . __LINE__ . '</span></fieldset>';
        }

        return $db2->db_connect_id->insert_id;
    } else {

        if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
            $status .= '</td></tr></table> '
                    . '<span class="bot_line">#' . __LINE__ . '</span></fieldset>';
        }

        if (isset($res) && $res === TRUE) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}

/**
 * достаём количество строчек в результате запроса
 * @global type $status
 * @param type $db
 * @param type $sql
 * @return type
 */
function getSqlNumRows($db, $sql) {

    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
        global $status;
        $status .= '<fieldset class="status" ><legend>' . __CLASS__ . ' #' . __LINE__ . ' + ' . __FUNCTION__ . '</legend>';
    }

    $sql2 = $db->sql_query($sql);
    $res = $db->sql_numrows($sql2);

    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
        $status .= '<span class="bot_line">#' . __LINE__ . '</span></fieldset>';
    }

    return $res;
}

/**
 * добавление многих записей в бд (PDO)
 * @param type $db
 * @param string $table
 * @param type $data
 * @param type $key
 * @param bool $slash
 * @param type $items_in_query
 */
function sql_insert_mnogo($db, string $table, $rows, $key = array(), bool $slash = true, $items_in_query = 500) {

    if (empty($rows)) {
        return false;
    }

    //\f\pa($table,2,null,'table');
    //\f\pa($key,2,null,'key');
    //\f\pa($rows,2,null,'rows');

    $list_polya = \f\db\pole_list($db, $table);
    // \f\pa($list_polya, 2, null, '$list_polya');
    // \f\pa($key , 2, null, '$key');
    // \f\pa($rows, 2, null, '$rows');

    try {

        $db->exec('BEGIN IMMEDIATE;');

        $indb = [];
        //$indb[':table'] = $table;
        $str_v = $sql_key_str = '';

        $nn = 1;

        foreach ($rows as $k => $v) {


            foreach ($key as $k0 => $v0) {
                $v[$k0] = $v0;
            }

            //\f\pa($v);
            $indb = [];
            $str_v2 = '';
            
            foreach ($v as $k1 => $v1) {

                if ($nn == 1)
                    $sql_key_str .= ( isset($sql_key_str{1}) ? ',' : '' ) . '`' . $k1 . '`';
//            $str_v .= ( isset($str_v{1}) ? ',' : '' ) . ' :'.$k1.' ';
//            $indb[':'.$k1] = $v1;
                $str_v2 .= ( isset($str_v2{1}) ? ',' : '' ) . ' :'.$k1.'_'.$nn.' ';
                $indb[':'.$k1.'_'.$nn] = $v1;
            }

        $s = 'INSERT INTO `' . $table . '` (' . $sql_key_str . ') VALUES (' . $str_v2 . ') ;';
        //echo $s;
        // \f\pa($indb, 2);
        $sql = $db->prepare($s);
        $sql->execute($indb);
            
            // $str_v .= ( isset($str_v{2}) ? ',' : '' ) . ' ( ' . $str_v2 . ' ) ';
            $nn++;
        }

        $db->exec('COMMIT;');
        return true;
        
        //die;
        
    } catch (\PDOException $ex) {

        echo '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
        . PHP_EOL . $ex->getMessage() . ' #' . $ex->getCode()
        . PHP_EOL . $ex->getFile() . ' #' . $ex->getLine()
        . PHP_EOL . $ex->getTraceAsString()
        . '</pre>';
    }

//    $db->exec('BEGIN IMMEDIATE;');
//    $stmt->execute($params);
//    $db->exec('COMMIT;');

    return true;
}

/**
 * добавление многих записей в бд (PDO) old
 * @param type $db
 * @param string $table
 * @param type $data
 * @param type $key
 * @param bool $slash
 * @param type $items_in_query
 */
function sql_insert_mnogo_old190424($db, string $table, $data, $key = array(), bool $slash = true, $items_in_query = 500) {

    if (sizeof($data) > 0) {
        
    } else {
        return false;
    }


    $list_polya = \f\db\pole_list($db, $table);
    // \f\pa($list_polya);

    $polya = array();

    if (isset($key) && sizeof($key) > 0) {
        foreach ($list_polya as $k => $v) {
            if (isset($key[$k]))
                $polya[$k] = $key[$k];
        }
    }

    foreach ($data as $k => $v) {
        foreach ($v as $k2 => $v2) {

            if (!isset($list_polya[$k2]))
                continue;

            if (!isset($polya[$k2]))
                $polya[$k2] = null;
        }
    }

// формируем стартовую строку +
    $sql_key_str = '';

    foreach ($polya as $k => $v) {
        $sql_key_str .= ( isset($sql_key_str{0}) ? ', ' : '' ) . '`' . $k . '`';
    }

    $sql_str = 'INSERT INTO `' . addslashes($table) . '` (' . $sql_key_str . ') VALUES ';

    // счётчик проходов цикла
    $ssa = 0;

    // запрос с данными
    $text = '';

    foreach ($data as $kk => $vv) {

        if ($ssa == $items_in_query && isset($text{10})) {

            // $b = $sql_str . ' ' . $text . ' ;';
            // echo '<br/><br/>' . $b;
            // $s2 = $db->prepare($b);
            $s2 = $db->prepare($sql_str . ' ' . $text . ' ;');
            //$s2 = $db->prepare($sql_str . ' ' . $text . ' ;');
            $s2->execute();

            $text = '';
            $ssa = 0;
        }

        $sql_text = '';
        // $sql_text = (isset($text{5})) ? ', ' : '' ;
        // echo '<br/>';
        // echo '<br/>';

        foreach ($polya as $k => $v) {

            //echo '<Br/>'.$k.' - '. $v ;

            if (isset($vv[$k])) {
                // echo '<br/>'.$k.'-'.$vv[$k];
                // echo '<br/>'.$k.'-'.$vv[$k].'-'.$v;
                $v = $vv[$k];
                // echo '<Br/>'.$v;
            }

            if (isset($v)) {

                if (isset($sql_text{0}))
                    $sql_text .= ', ';

                // echo '<br/>'.$k.'-'.$v;

                if ($v == '' || $v === null) {
                    $sql_text .= 'NULL';
                } elseif ($v == 'NOW') {
                    $sql_text .= 'NOW()';
                } else {
                    $sql_text .= '\'' . addslashes($v) . '\'';
                }
            } else {

                if (isset($sql_text{0}))
                    $sql_text .= ', ';

                $sql_text .= 'NULL';
            }
        }

        $text .= ( (isset($text{5})) ? ', ' : '' ) . '(' . $sql_text . ')';

        $ssa++;
    }

// если что то осталось то выполняем запрос    
    if (isset($text{5})) {

        $b = $sql_str . ' ' . $text . ' ;';
        echo '<br/><br/>' . $b;

        $s2 = $db->prepare($b);
        //$s2 = $db->prepare($sql_str . ' ' . $text . ' ;');
        $s2->execute();
    }
}

function pole2_list_old($db, $table, $prosto = FALSE) {
    return pole_list($db, $table, $prosto);
}

function pole_list_old($db, $table, $prosto = FALSE) {

    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
        global $status;
        $status .= '<fieldset class="status" ><legend>' . __CLASS__ . ' #' . __LINE__ . ' + ' . __FUNCTION__ . '</legend>';
    }

    $FileCh = $_SERVER['DOCUMENT_ROOT'] . '/0.zip/mysql-table_polya-' . $table . '.arr.cash24';
// echo filemtime($FileCh)-(3600*24*5).' > '.$_SERVER['REQUEST_TIME'];

    if (is_file($FileCh) && ( filectime($FileCh) + (3600 * 48) ) > $_SERVER['REQUEST_TIME'] && (!isset($_SESSION['status1']) || ( isset($_SESSION['status1']) && $_SESSION['status1'] !== true ) )) {

        $ar = unserialize(file_get_contents($FileCh));
    } else {

        if (is_file($FileCh))
            unlink($FileCh);

        $ar = array();

        $sql = $db->sql_query('SELECT INFORMATION_SCHEMA.COLUMNS.* FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME=\'' . addslashes($table) . '\' ;');
        if ($db->sql_numrows($sql) > 0) {
            while ($res = $db->sql_fr($sql)) {
                $ar[$res['COLUMN_NAME']] = $res;
            }
        }

        file_put_contents($FileCh, serialize($ar));
    }

    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
        $status .= f\pa2($ar) . '<span class="bot_line">#' . __LINE__ . '</span></fieldset>';
    }

    return $ar;
}

function SqlMind_insert_mnogo($db, $table, $SlKey, $data, $slash = FALSE, $delaed = true) {
    global $status;
    $status .= 'коунт дата: ' . count($data);

    if ($SlKey === false) {
        $SlKey = $data[0];
    }

    /*
      INSERT INTO `mod_1s_img` ( `id` , `id_shop` , `file` , `new_name` , `type1s` , `type` , `add_date` , `add_time` , `status` , `del_date` ) VALUES
      (NULL , '1', '2', '3', 'name', 'fresh', NOW( ) , NOW( ) , 'load', NULL),
      (NULL , '2', '3', '4', 'name', 'tovar', NOW( ) , NOW( ) , 'load', NULL);
     */

    $SlNwKolvo = 1000;
    $text = '';
    $SlS = $ssa = 0;

    $SqlStr = 'INSERT ' . ( $delaed === true ? 'DELAYED ' : '' ) . 'INTO `' . addslashes($table) . '` (';

    foreach ($SlKey as $k => $v) {

        if ($SlS == 1)
            $SqlStr .= ', ';

        $SlKey[] = $k;
        $SqlStr .= '`' . $k . '`';

        $SlS = 1;
    }

    $SqlStr .= ') VALUES ';

    unset($table);
    //echo $SqlStr; exit();
    //$status = '';
    $colvo = sizeof($data);

    if ($colvo > 0) {
        for ($w = 0; $w < $colvo; $w++) {
            if ($ssa == $SlNwKolvo) {
                $db->sql_query($SqlStr . ' ' . $text . ' ;');
                $text = '';
                $ssa = 0;
            }

            if ($ssa > 0)
                $text .= ', ';

            if (count($data[$w]) >= 1) {
                $rfhn = $rafraf = 0;
                $ortext = '';

                foreach ($SlKey as $k => $v) {
                    if (!is_numeric($k)) {
                        if ($rafraf == 1)
                            $ortext .= ', ';

                        if (empty($data[$w][$k])) {

                            if ($v == 'NOW') {
                                $ortext .= "NOW()";
                            } else {
                                $ortext .= ( $v == 1 ) ? "NULL" : '\'' . addslashes($v) . '\'';
                            }
                        } else {
                            if ($data[$w][$k] == 'NOW') {
                                $ortext .= "NOW()";
                                $rfhn = 1;
                            } else {
                                $ortext .= "'" . addslashes($data[$w][$k]) . "'";
                                $rfhn = 1;
                            }
                        }
                        $rafraf = 1;
                    }
                }

                if ($rfhn != 0) {
                    $text .= '(' . $ortext . ')';
                    $ssa++;
                }
                unset($data[$w]);
            }
        }
        $db->sql_query($SqlStr . ' ' . $text . ' ;');
    }

    // echo $status; //exit();
}

function SqlInMn($start, $data) {
    global $db;
    $status = '';
    //global $db,$status;

    /* INSERT INTO `mod_1s_img` ( `id` , `id_shop` , `file` , `new_name` , `type1s` , `type` , `add_date` , `add_time` , `status` , `del_date` ) VALUES
      (NULL , '1', '2', '3', 'name', 'fresh', NOW( ) , NOW( ) , 'load', NULL),
      (NULL , '2', '3', '4', 'name', 'tovar', NOW( ) , NOW( ) , 'load', NULL); */

    $kolvo = 500;
    $text = '';
    $ssa = 0;

    $status .= 'коунт дата: ' . count($data);

    if (count($data) >= 1) {
        //echo __LINE__.' ['.count($data).']</br>';
        //$status = '';

        for ($w = 0; $w < count($data); $w++) {
            //echo __LINE__.'</br>';

            if ($ssa == $kolvo) {
                //echo __LINE__.'</br>';
                $db->sql_query($start . ' ' . $text . ';');
                $text = '';
                $ssa = 0;
            }

            if ($ssa > 0)
                $text .= ', ';

            //if( strlen( $data[$w] ) > 5 )
            if (isset($data[$w]{5})) {
                $text .= $data[$w];
                $ssa++;
            }
            //echo __LINE__.'</br>';
        }
        //$status = '';
        $db->sql_query($start . ' ' . $text . ';');
        //echo $status;
    }
    unset($start, $text, $ssa);
}

function GenSqlQuery($from, $select, $day, $group, $sort) {
    global $db, $status;

    $sql = "SELECT " . $select . " FROM `" . $from . "` WHERE ";

    //reset ($day);
    $rf = 1;

    //while (list($k, $v) = each($day))
    //{
    foreach ($day as $k => $v) {
        if ($rf != 1)
            $sql .= " AND ";

        if ($v == '') {
            $sql .= "`" . $k . "` IS NULL ";
        } elseif (strpos($v, "`") !== false) {
            $sql .= "`" . $k . "` = " . $v . " ";
        } elseif (strpos($k, "non.") !== false) {
            $sql .= "`" . substr($k, 4) . "` != '" . $v . "' ";
        } else {
            $sql .= "`" . $k . "` = '" . $v . "' ";
        }
        $rf++;
    }

    if (strlen($group) > 1)
        $sql .= "GROUP BY `" . $group . "` ";
    if (strlen($sort) > 1)
        $sql .= "ORDER BY " . $sort . " ";


    $sql .= " " . $end . " ;";
    return $sql;
}

/**
 * Редактирование 1-ной записи в таблице PDO
 * @global type $status
 * @param класс $db
 * @param type $table
 * @param type $keys
 * @param type $data
 * @param type $replace_keys
 * возможность замены ключей участвующих в выборке
 * false - запрет замены / true - разрешение замены ключей участвующих в выборке
 * @param type $limit
 * @param type $slash
 * @return boolean
 */
function db_edit2($db, string $table, $keys, array $data, $replace_keys = false, $limit = 1, $slash = 'ne') {

    if (sizeof($data) == 0)
        return FALSE;

    // \f\pa($keys);

    $polya = \f\db\pole_list($db, $table);
    // \f\pa($form_polya);
    // массив в запрос
    $in_var = [];
    $keys2 = array();

    $where = '';
    foreach ($keys as $k => $v) {
        if (isset($polya[$k])) {

            $where .= ( isset($where{3}) ? 'AND' : '' ) . ' `' . $k . '`= :key_' . $k . ' ';
            $in_var[':key_' . $k] = $v;

            if ($replace_keys === false)
                $keys2[$k] = 1;
        }
    }

    $sql_set = '';
    foreach ($data as $key => $val) {

        if (isset($key) && isset($polya[$key])) {


            // Пропускаем $key так как этот ключ участвует в выборке а замена запрещена
            if (isset($keys2[$key]))
                continue;

            $sql_set .= ' ' . ( isset($sql_set{1}) ? ', ' : '' ) . '`' . $key . '` = :val_' . $key;
            $in_var[':val_' . $key] = $val;
        }
    }

    $sql = $db->prepare('UPDATE `' . $table . '` '
            . ' SET ' . $sql_set
            . ( isset($where{0}) ? ' WHERE ' . $where : '' )
            // . ( (isset($limit) && is_numeric($limit)) ? ' LIMIT ' . $limit : '' )
            . ';');

    if ($sql->execute($in_var)) {
        return TRUE;
    } else {
        return FALSE;
    }
}

//  Редактирование 1-ной записи в таблице
function db_edit($table, $wskey_pole, $wsznach, $var_array, $limit = 1, $slash = 'ne') {

    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
        global $status, $db;

        $status .= '<fieldset class="status" ><legend>' . __CLASS__ . ' #' . __LINE__ . ' + ' . __FUNCTION__ . '</legend>';
    } else {
        global $db;
    }

    $r = \f\db\db_edit2($db, $table, $keys, $data, false, $limit, $slash);

    if (isset($_SESSION['status1']) && $_SESSION['status1'] === true) {
        $status .= '<span class="bot_line">#' . __LINE__ . '</span></fieldset>';
    }

    return $r;
}

function db_edit_old($table, $wskey_pole, $wsznach, $var_array, $limit = 1, $slash = 'ne') {

    //GLOBAL $db, $status;
    GLOBAL $db;
    $all_s = '';

    // вряме на выполнение
    if (function_exists('timer')) {
        $timer_rand = rand(5654, 987987);
        timer('db_insert' . $timer_rand, 'старт');
    }

    $status .= '<fieldset><legend>функция db_edit</legend>' .
            'Используется таблица <strong>' . $table . '</strong>.<br>';

    if (!is_array($var_array)) {
        $status .= '<h1>Нет массива для записи. Операция отменена</h1></fieldset>';
        return FALSE;
    }

    //reset ($var_array);
    // перебираем массив с переменными и компилируем запрос на сохранение переменных
    $zpt = 0;

    $AllFieldName = pole_list($table, 'name');

    //while (list($key, $val) = each($var_array))
    //{
    foreach ($var_array as $key => $val) {
        $status .= 'Переменная (поле) <strong>' . $key . '</strong> = <u>' . $val . '</u><br>';

        if (isset($key) && isset($AllFieldName[$key]) && $AllFieldName[$key] == 1) {
            if ($zpt != 0)
                $all_s .= ',';

            //if ( strlen($val)<0 || $val == '')
            if ($val == '' || $val == 'NULL') {
                $val = ' NULL';
            } else {
                if ($val == 'NOW' || $val == 'NOW()' || $val == 'NOW( )') {
                    $val = ' NOW()';
                } else {
                    if (isset($slash) && $slash != 'ne') {
                        $val = " '" . addslashes($val) . "'";
                    } else {
                        $val = " '" . $val . "'";
                    }
                }
            }
            $all_s .= "`" . $key . "` =" . $val;
            $zpt = 1;
        }
    }


    $lim = '';

    if (isset($limit) && is_numeric($limit))
        $lim = ' LIMIT ' . $limit;

    $res = $db->sql_query("UPDATE `" . $table . "` SET " . $all_s . "
            WHERE `" . $wskey_pole . "` = '" . $wsznach . "'
            " . $lim . ";");

    // вряме на выполнение
    if (function_exists('timer')) {
        timer('db_insert' . $timer_rand, 'конец');
        timer('db_insert' . $timer_rand, 'итог');
    }

    $status .= '</fieldset>';

    if ($res == TRUE) {
        return TRUE;
    } else {
        return FALSE;
    }
}

//  Добавление записи в таблицу
/*
  function db_insert($table, $var_array, $slah = 'no') {
  GLOBAL $db, $status, $debug;

  // вряме на выполнение
  if (function_exists('timer')) {
  $timer_rand = rand(5654, 987987);
  timer('db_insert' . $timer_rand, 'старт');
  }

  $status .= '<table><tr><td width="15" bgcolor="#DDDD33" align="center" nowrap style="direction: ltr; writing-mode:  tb-rl;"><b>DB_insert</b></td><td bgcolor="#EFedDE"> <b>функция db_insert</b> <br>';
  $status .= 'Используется таблица <strong>' . $table . '</strong>.<br>';
  //reset ($var_array);
  // перебираем массив с переменными и компилируем переменные
  $zpt = 0;
  $AllFieldName = pole_list($table, 'name');

  //while (list($key, $val) = each($var_array))
  //{
  foreach ($var_array as $key => $val) {
  $status .= 'Переменная <strong>' . $key . '</strong> = <u>' . $val . '</u><br>';

  if (isset($key) && isset($AllFieldName[$key]) && $AllFieldName[$key] == 1) {
  if ($zpt != 0) {
  $all_val .= ',';
  $all_key .= ',';
  }

  if (strlen($val) < 0 || $val == '') {
  $all_val .= ' NULL';
  $debug['sql_struktura'][$key] = $val;
  } else {
  if (!isset($all_val))
  $all_val = '';

  if ($val == 'NOW()' || $val == 'NOW') {
  $all_val .= ' NOW()';
  } else {
  $all_val .= ($slah == 'no') ? " '" . $val . "'" : " '" . addslashes($val) . "'";
  }
  $debug['sql_struktura'][$key] = $val;
  }

  if (!isset($all_key)) {
  $all_key = '';
  }

  $all_key .= ' `' . $key . '`';
  $zpt++;
  }
  }

  if (isset($all_key) && isset($all_val))
  $res = $db->sql_query("INSERT INTO `" . $table . "` ( " . $all_key . " ) VALUES ( " . $all_val . " );");

  //$status .= '<strong>all key:</strong> ['.$all_key.']<br>' ;
  //$status .= '<strong>all val:</strong> ['.$all_val.']' ;
  // вряме на выполнение
  if (function_exists('timer')) {
  timer('db_insert' . $timer_rand, 'конец');
  timer('db_insert' . $timer_rand, 'итог');
  }

  $status .= '</td></tr></table> ';
  if (isset($res) && $res === TRUE) {
  return TRUE;
  } else {
  return FALSE;
  }
  }
 */

function db_kolvo($dir) { // возвращает количество результатов
    GLOBAL $db, $status;

    $resultat = $db->sql_query($dir);
    $resultatik = $db->sql_numrows($resultat);

    $status .= '<b>function db kolvo</b> запрос - ' . $dir . ' результат - <b>' . $resultatik . '</b><br>';
    return $resultatik;
}

function query($var, $table, $id, $option = "") {
    global $db, $status;
    $status .= '<table><tr><td width="15" bgcolor="#DDEE55" align="center" nowrap style="direction: ltr; writing-mode:  tb-rl;"><strong>query</strong></td><td bgcolor="#E0e0D0">';

    // вряме на выполнение
    if (function_exists('timer')) {
        $timer_rand = rand(5654, 987987);
        timer('query' . $timer_rand, 'старт');
    }

    $sql = "SELECT " . $var . " FROM `" . $table . "` WHERE " . $id;
    $result = $db->sql_query($sql);

    if ($option == 'numrows') {
        // вряме на выполнение
        if (function_exists('timer')) {
            timer('query' . $timer_rand, 'конец');
            timer('query' . $timer_rand, 'итог');
        }

        $status .= '<strong>numrows</strong></td></tr></table> ';
        return $db->sql_numrows($result);
    } elseif ($option == 'one') {
        $ert = $db->sql_fetchrow($result);
        // вряме на выполнение
        if (function_exists('timer')) {
            timer('query' . $timer_rand, 'конец');
            timer('query' . $timer_rand, 'итог');
        }
        $status .= '<strong>one</strong></td></tr></table> ';
        return $ert[0];
    } elseif ($option == 'allone') {
        $ert = $db->sql_fetchrow($result);
        // вряме на выполнение
        if (function_exists('timer')) {
            timer('query' . $timer_rand, 'конец');
            timer('query' . $timer_rand, 'итог');
        }
        $status .= '<strong>allone</strong></td></tr></table> ';
        return $ert;
    } elseif ($option == 'array') {
        $arrayz = array();
        while ($ert = $db->sql_fetchrow($result)) {
            $arrayz[] = $ert[0];
        }
        // вряме на выполнение
        if (function_exists('timer')) {
            timer('query' . $timer_rand, 'конец');
            timer('query' . $timer_rand, 'итог');
        }
        $status .= '<strong>array</strong></td></tr></table> ';
        return $arrayz;
    } elseif ($option == 'summa') {
        $sum = 0;
        while ($ert = $db->sql_fetchrow($result)) {
            $sum = $sum + $ert[0];
        }
        // вряме на выполнение
        if (function_exists('timer')) {
            timer('query' . $timer_rand, 'конец');
            timer('query' . $timer_rand, 'итог');
        }
        $status .= '<strong>summa</strong></td></tr></table> ';
        return $sum;
    } else {
        // вряме на выполнение
        if (function_exists('timer')) {
            timer('query' . $timer_rand, 'конец');
            timer('query' . $timer_rand, 'итог');
        }
        $status .= '<strong>1 row</strong></td></tr></table> ';
        return $db->sql_fetchrow($result);
    }
}

function SqlMaxInsert($start, $data) {
    //global $db;
    //$status = '';
    global $db, $status;

    /* INSERT INTO `mod_1s_img` ( `id` , `id_shop` , `file` , `new_name` , `type1s` , `type` , `add_date` , `add_time` , `status` , `del_date` ) VALUES
      (NULL , '1', '2', '3', 'name', 'fresh', NOW( ) , NOW( ) , 'load', NULL),
      (NULL , '2', '3', '4', 'name', 'tovar', NOW( ) , NOW( ) , 'load', NULL); */

    $kolvo = 100;
    $text = '';
    $ssa = 0;

    $status .= 'коунт дата: ' . count($data);

    if (count($data) >= 1) {
        //echo __LINE__.' ['.count($data).']</br>';
        //$status = '';

        for ($w = 0; $w < count($data); $w++) {
            //echo __LINE__.'</br>';

            if ($ssa == $kolvo) {
                //echo __LINE__.'</br>';
                $db->sql_query($start . ' ' . $text . ';');
                $text = '';
                $ssa = 0;
            }

            if ($ssa > 0)
                $text .= ', ';

            if (isset($data[$w] {4})) {
                $text .= $data[$w];
                $ssa++;
            }
            //echo __LINE__.'</br>';
        }
        //$status = '';
        $db->sql_query($start . ' ' . $text . ';');
        //echo $status;
    }
    //echo $status;
    unset($start, $text, $ssa);
}

/*
function save_string_to($db, $tablefrom, $name, $key, $tableto, $added) {
    global $status;

    $status = '';
    // $L2233 = ;
    // echo $status;

    $da22 = $db->sql_fetchrow_assoc($db->sql_query('SELECT *
            FROM `' . $tablefrom . '`
            WHERE `' . $name . '` = \'' . addslashes($key) . '\'
            LIMIT 1;'));
    echo '<pre>';
    print_r($da22);
    echo '</pre>';

    // $pole1 = ' `save_dati`, `save_agent`';
    // $pole2 = ' \''.$_SERVER['REQUEST_TIME'].'\', \'\'';
    $pole1 = $pole2 = '';

    // $datas2 = array_merge( $da22, $added);
    // $datas2 = $da22 + $added;

    foreach ($added as $kl => $vl) {
        $da22[$kl] = $vl;
    }

    foreach ($da22 as $k => $vv) {

        if (isset($pole1{1}))
            $pole1 .= ', ';

        $pole1 .= '`' . $k . '`';

        if (isset($pole2{1}))
            $pole2 .= ', ';

        $pole2 .= '\'' . addslashes(stripslashes($vv)) . '\'';
    }
    // $status = '';
    $db->sql_query('INSERT INTO `' . $tableto . '` ( ' . $pole1 . ' ) ' .
            'VALUES ( ' . $pole2 . ' );');
    echo $status;

    return true;
}

*/

/*
if (!function_exists('mysql_dump')) {

    function mysql_dump($database) {

        $query = '';

        $tables = @mysql_list_tables($database);
        while ($row = @mysql_fetch_row($tables)) {
            $table_list[] = $row[0];
        }

        for ($i = 0; $i < @count($table_list); $i) {

            $results = mysql_query('DESCRIBE ' . $database . '.' . $table_list[$i]);

            $query .= 'DROP TABLE IF EXISTS `' . $database . '.' . $table_list[$i] . '`;' . lnbr;
            $query .= lnbr . 'CREATE TABLE `' . $database . '.' . $table_list[$i] . '` (' . lnbr;

            $tmp = '';

            while ($row = @mysql_fetch_assoc($results)) {

                $query .= '`' . $row['Field'] . '` ' . $row['Type'];

                if ($row['Null'] != 'YES') {
                    $query .= ' NOT NULL';
                }
                if ($row['Default'] != '') {
                    $query .= ' DEFAULT \'' . $row['Default'] . '\'';
                }
                if ($row['Extra']) {
                    $query .= ' ' . strtoupper($row['Extra']);
                }
                if ($row['Key'] == 'PRI') {
                    $tmp = 'primary key(' . $row['Field'] . ')';
                }

                $query .= ',' . lnbr;
            }

            $query .= $tmp . lnbr . ');' . str_repeat(lnbr, 2);

            $results = mysql_query('SELECT * FROM ' . $database . '.' . $table_list[$i]);

            while ($row = @mysql_fetch_assoc($results)) {

                $query .= 'INSERT INTO `' . $database . '.' . $table_list[$i] . '` (';

                $data = Array();

                while (list($key, $value) = @each($row)) {
                    $data['keys'][] = $key;
                    $data['values'][] = addslashes($value);
                }

                $query .= join($data['keys'], ', ') . ')' . lnbr . 'VALUES (\'' . join($data['values'], '\', \'') . '\');' . lnbr;
            }

            $query .= str_repeat(lnbr, 2);
        }

        return $query;
    }

}
*/
