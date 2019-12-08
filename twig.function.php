<?php

/**
  определение функций для TWIG
 */
//creatSecret
// $function = new Twig_SimpleFunction('creatSecret', function ( string $text ) {
//    return \Nyos\Nyos::creatSecret($text);
// });
// $twig->addFunction($function);


$function = new Twig_SimpleFunction('f__translit', function ( string $cyr_str, $type = false ) {
    return \f\translit($cyr_str, $type);
});
$twig->addFunction($function);






$function = new Twig_SimpleFunction('didrive_f__timer_start', function ( $timer_id = '' ) {
    \f\CalcMemory::start($timer_id);
    return \f\timer::start($timer_id);
});
$twig->addFunction($function);

$function = new Twig_SimpleFunction('didrive_f__timer_stop', function ( $timer_id = '' ) {
    return \f\timer::stop('str', $timer_id);
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


