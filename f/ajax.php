<?php

namespace nyos\f;

class ajax {

    public function __construct() {
        echo '<br/>start ' . __CLASS__ . '<br/>';
        echo __FILE__ . ' [' . __LINE__ . ']<br/>';
    }

    /**
     * заглушка возврат массива f\end2( array )
     * @param type $html
     * @param type $stat
     * @param type $dop_array
     * @return type
     */
    public static function end3($html, $stat = true, $dop_array = false) {
        return end2($html, $stat, array('data' => $dop_array), 'array');
    }

    /**
     * возврат в конце чего нить
     * @param type $html
     * @param type $status
     *
     * @param type $dop_array
     * @param type $type2
     * array - return / table - die / (default) json - die
     * @return type
     */
    public static function end2( string $html, $stat = true, $dop_array = false, $type2 = 'json') {

        $t = ( $dop_array !== false ) ? $dop_array : array();

        $t['status'] = ( $stat == 'ok' || $stat === true ) ? 'ok' : 'error';
        $t['html'] = $html;

        // if ($dop_array !== false)
        // $t = array_merge($t, $dop_array);
        // используется только для отладки
        // debug_print_backtrace();
        if ($type2 == 'array') {

            return $t;
        }
        elseif ($type2 == 'table') {

            echo '<table>';
            foreach ($t as $k => $k2) {
                echo '<tr><td>' . $k . '</td><td>' . $k2 . '</td></tr>';
            }
            if ($dop_array !== false) {
                echo '<tr><td>dop array</td><td>+</td></tr>';
                foreach ($dop_array as $k => $k2) {
                    echo '<tr><td>' . $k . '</td><td>' . $k2 . '</td></tr>';
                }
            }
            echo '</table>';

            die();
        } else {

            die(json_encode($t));
        }
    }

}
