<?php

/**
  определение функций для TWIG
 */
//creatSecret
// $function = new Twig_SimpleFunction('creatSecret', function ( string $text ) {
//    return \Nyos\Nyos::creatSecret($text);
// });
// $twig->addFunction($function);

$function = new Twig_SimpleFunction('didrive_f__timer_start', function ( $n = null ) {

    \f\timer::start( $n );
    return;
});
$twig->addFunction($function);

$function = new Twig_SimpleFunction('didrive_f__timer_stop', function ( $n = null ) {

    return \f\timer::stop( $n );
});
$twig->addFunction($function);
