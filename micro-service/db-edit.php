<?php

if (isset($skip_start) && $skip_start === true) {
    
} else {
    require_once '0start.php';
}

// table
// s = $in['table'] . '-' . $in['pole'] . '-' . $in['item_id'] . '-' . $in['value']

try {

    $in = $_REQUEST;

    if (!empty($in['in_table']))
        $in['table'] = $in['in_table'];

    if (!empty($in['in_pole']))
        $in['pole'] = $in['in_pole'];

    if (!empty($in['in_item_id']))
        $in['item_id'] = $in['in_item_id'];

    if (!empty($in['in_s']))
        $in['s'] = $in['in_s'];

    if (!empty($in['new_val']))
        $in['value'] = $in['new_val'];

    if (!empty($in['s']) && !empty($in['table'])
            //
            && !empty($in['pole']) && !empty($in['item_id'])
            //
            && !empty($in['value']) && !empty($in['s'])
    ) {


        if (
                ( \Nyos\Nyos::checkSecret($in['s'], $in['table'] . '-' . $in['pole'] . '-' . $in['item_id'] . '-' . $in['value']) !== false ) 
                //
                || ( \Nyos\Nyos::checkSecret($in['s'], $in['table'] . '-' . $in['pole'] . '-' . $in['item_id']) !== false )
        ) {

            $sql = 'UPDATE `' . addslashes($in['table']) . '` SET `' . addslashes($in['pole']) . '` = :in WHERE `id` = :id ;';
            $ff = $db->prepare($sql);
            $in_sql_val = [
                ':in' => $in['value'],
                ':id' => $in['item_id']
            ];
            $ff->execute($in_sql_val);

            if ($ff->rowCount() == 0) {
                \f\end2('запрос отправлен, изменения не сохранены #' . __LINE__, false);
            } else {
                \f\end2('изменения сохранены', true);
            }
        } else {
            \f\end2('не окей c #' . __LINE__, false);
        }
        
    }
    
} catch (\PDOException $exc) {

    \f\end2('не окей', false, $exc);
}

\f\end2('что то пошло не так #' . __LINE__, false, $in );
