<?php

/* You can specify a custom function */

//$twig->addFilter(new Twig_SimpleFilter( 'money', function( $value, $currency, $prefix = false, $decimals = 2, $dec_point = ".", $thousands_sep = ",") {
//            $value = number_format($value, $decimals, $dec_point, $thousands_sep);
//
//            if ($prefix)
//                return $currency . ' ' . $value;
//
//            return $value . ' ' . $prefix;
//        }));

/**
 * показ сотовых номеров в норм виде
 * type = 8rus 8-999-888-77-66
 * type = 7rus +7-999-888-77-66
 */
$twig->addFilter(new Twig_SimpleFilter('phone', function( $value, $type = '8rus' ) {

// $result = preg_replace("/[^,.0-9]/", '', $value);
            $str = preg_replace("/[^0-9]/", '', $value);

            if (strlen($str) == 11) {

                $b1 = substr($str, 0, 1);

                if ($type == '8rus') {
                    if ($b1 == 7 || $b1 == 8) {
                        return '8-' . substr($str, 1, 3) . '-' . substr($str, 4, 3) . '-' . substr($str, 7, 2) . '-' . substr($str, 9, 2);
                    }
                } elseif ($type == '7rus') {
                    if ($b1 == 7 || $b1 == 8) {
                        return '7-' . substr($str, 1, 3) . '-' . substr($str, 4, 3) . '-' . substr($str, 7, 2) . '-' . substr($str, 9, 2);
                    }
                } elseif ($type == '+7rus') {
                    if ($b1 == 7 || $b1 == 8) {
                        return '+7-' . substr($str, 1, 3) . '-' . substr($str, 4, 3) . '-' . substr($str, 7, 2) . '-' . substr($str, 9, 2);
                    }
                }

                // return substr($str, 0, 1) . '-' . substr($str, 1, 3) . '-' . substr($str, 4, 3) . '-' . substr($str, 7, 2) . '-' . substr($str, 9, 2);
            }

            // return '-';
            return $str;

//            $value = number_format($value, $decimals, $dec_point, $thousands_sep);
//
//            if ($prefix)
//                return $currency . ' ' . $value;
//
//            return $value . ' ' . $prefix;
        }));

/**
 * вернуть только цифры из строки
 */
$twig->addFilter(new Twig_SimpleFilter('f__txt__onlyNumbers', function( $sring ) {
            return preg_replace('/\s+/', '', $sring );
        }));
