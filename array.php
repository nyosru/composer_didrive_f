<?php

namespace f;


/**
 * ищем массив в массиве
 * @param type $ar
 * @param type $search_key
 * @param type $search_val
 * @return boolean
 */
    function find_array( $ar, $search_key, $search_val ) {
        
        foreach( $ar as $k => $v ){
            if( $v[$search_key] == $search_val ){
                return $v;
            }
        }
        
        return false;
    }

