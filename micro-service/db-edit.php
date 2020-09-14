<?php

try {

    require_once '0start.php';

    $in = $_REQUEST;

    if (!empty($in['table'])) {
        $table = htmlspecialchars($in['db_table']);
    } else {
        \f\end2('не определена таблица', false, $in);
    }

    if (!empty($in['s']) && !empty($in['table'])
            //
            && !empty($in['pole']) && !empty($in['item_id'])
            //
            && !empty($in['value']) && !empty($in['s'])
    ) {


        if (\Nyos\Nyos::checkSecret($in['s'], $in['table'] . '-' . $in['pole'] . '-' . $in['item_id'] . '-' . $in['value']) !== false) {
            $sql = 'UPDATE `' . addslashes($in['table']) . '` SET `' . addslashes($in['pole']) . '` = :in WHERE `id` = :id ;';
            $ff = $db->prepare($sql);
            $in_sql_val = [
                ':in' => $in['value'],
                ':id' => $in['item_id']
            ];
            $ff->execute($in_sql_val);
            \f\end2('изменения сохранены', true);
        } else {
            \f\end2('не окей c #' . __LINE__, false);
        }
    }
} catch (\PDOException $exc) {

    \f\end2('не окей', false, $exc);
}

\f\end2('что то пошло не так #' . __LINE__, false);
