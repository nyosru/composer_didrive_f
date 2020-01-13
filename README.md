Russian

----- Установка ----- 

composer require didrive/f

----- Пример -----

первый раз сохраняем, второй и дальше тащим с оперативки

    $cash_var = '************';
    $e = \f\Cash::getVar($cash_var);
    if (!empty($e)) {
        return $e;
    }

+++++++ код +++++++

    \f\Cash::setVar($cash_var, $return);


-------- работа с картинками -------------

type min 
w_min - ширина в пикселях

type fix_w
w - ширина в пикселях

// делаем квадрат из фотки всё что вогрук заливаем этой же фоткой
type - kv_fill
w - ширина высота

htaccess
    RewriteRule ^di-img/(.*)/(.*)/(.*)/(.*)/(.*)\.(webp|gif|GIF|JPG|jpg|JPEG|jpeg|png)$	/vendor/didrive/f/img.ajax.php?type=$1&w=$2&uri=$3/$4/$5.$6 [L]
    RewriteRule ^di-img/(.*)/(.*)/(.*)/(.*)\.(webp|gif|GIF|JPG|jpg|JPEG|jpeg|png)$	/vendor/didrive/f/img.ajax.php?type=$1&w=$2&uri=$3/$4.$5 [L]
    RewriteRule ^di-img/(.*)/(.*)/(.*)\.(webp|gif|GIF|JPG|jpg|JPEG|jpeg|png)$	/vendor/didrive/f/img.ajax.php?type=$1&w=$2&uri=$3.$4 [L]
