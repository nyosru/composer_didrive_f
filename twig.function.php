<?php

/**
  определение функций для TWIG
 */
//creatSecret
// $function = new Twig_SimpleFunction('creatSecret', function ( string $text ) {
//    return \Nyos\Nyos::creatSecret($text);
// });
// $twig->addFunction($function);



$function = new Twig_SimpleFunction('f__md5', function ( string $str ) {
    return md5($str);
});
$twig->addFunction($function);

$function = new Twig_SimpleFunction('f__translit', function ( $cyr_str = '', $type = false ) {
    
    if (empty($cyr_str))
        return '';
    
    return \f\translit($cyr_str, $type);
});
$twig->addFunction($function);

$function = new Twig_SimpleFunction('f__get_ext', function ( string $f ) {
    return \f\get_file_ext($f);
});
$twig->addFunction($function);






$function = new Twig_SimpleFunction('didrive_f__timer_start', function ( $timer_id = '' ) {
//    \f\CalcMemory::start($timer_id);
//    return \f\timer::start($timer_id);
    \f\timer_start($timer_id);
});
$twig->addFunction($function);

$function = new Twig_SimpleFunction('didrive_f__timer_stop', function ( $timer_id = '' ) {
    //return \f\timer::stop('str', $timer_id);
    return \f\timer_stop($timer_id);
});
$twig->addFunction($function);


$function = new Twig_SimpleFunction('didrive_f__memory_stop', function ( $timer_id = '' ) {
    return [
        'timer' => \f\timer::stop('str', $timer_id),
        'memory' => \f\CalcMemory::stop($timer_id)
    ];
});
$twig->addFunction($function);





$function = new Twig_SimpleFunction('pa', function ( $ar, $type = null ) {

    if ($type == 2) {

        echo '<pre style="max-height:200px;overflow:auto;" >';
        print_r($ar);
        echo '</pre>';
    } else {

        echo '<pre>';
        print_r($ar);
        echo '</pre>';
    }

    return;
});
$twig->addFunction($function);



$function = new Twig_SimpleFunction('f__http_build_query', function ( array $ar ) {
    return http_build_query($ar);
});
$twig->addFunction($function);


require_once 'txt.twig.function.php';
