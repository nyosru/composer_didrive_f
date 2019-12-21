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
