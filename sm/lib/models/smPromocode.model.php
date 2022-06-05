<?php

class smPromocodeModel extends waModel
{
    protected $table = 'sm_promocode';

    public function listEntities($start, $length, $order, $column, $direction, $search)
    {
        $order_by = array(
            0 => 'datetime_start',
            1 => 'datetime_end',
            2 => 'count',
            3 => 'id',
            4 => 'name',
        );

        // Вычисление общего количества записей
        $rec_total = $this->query("SELECT COUNT(id) AS total FROM ".$this->table." WHERE hidden = 0 ")->fetchAll();
        $rec_total = $rec_total[0]['total'];

        // Базовый запрос
        $query = "SELECT * FROM ".$this->table." WHERE hidden = 0";

        // Поиск
        if($search)
        {
            $query = $query." AND (id = ".intval($search)." OR name LIKE ('%".$this->escape($search)."%'))";
        }

        // Сортировка
        if($order)
        {
            if(isset($order_by[$column])) {$query .= " ORDER BY ".$order_by[$column]." ".$direction;}
            else {$query .= " ORDER BY ".$order_by[0]." ".$direction;}
        }
        $query_no_limit = $query;

        // Количество
        $query.=" LIMIT ".intval($start).",".intval($length);

        // Выполнение поиска
        $promocodes = $this->query($query)->fetchAll();
        $user_filtered = $this->query($query_no_limit)->count();

        $result = array();
        $result['data'] =array();// $tariffs;

        if($user_filtered > 0)
        {
            foreach($promocodes as $promocode)
            {
                if(strtotime($promocode['datetime_end']) < strtotime(date("Y-m-d")) )
                {
                    $check = 'Истек срок действия';
                }else{
                    $check = ($promocode['count'] > 0) ? 'Используется' : 'Закончился';
                }

                array_push($result['data'], array(
                    /*$promocode['id'],*/
                    '<a href="?module=clients&action=promocode&id='.$promocode['id'].'">'.htmlspecialchars($promocode['name'], ENT_QUOTES).'</a>',
                    '<a href="?module=clients&action=promocode&id='.$promocode['id'].'">'.htmlspecialchars($promocode['promocode'], ENT_QUOTES).'</a>',
                    $check,
                    '<a href="?module=clients&action=promocode&id='.$promocode['id'].'">'.htmlspecialchars($promocode['count'], ENT_QUOTES).'</a>',
                    '<a href="?module=clients&action=promocode&id='.$promocode['id'].'">'.htmlspecialchars($promocode['datetime_end'], ENT_QUOTES).'</a>',
                ));
            }
        }

        $result['recordsTotal'] = $rec_total;
        if(!$result['recordsTotal']) {$result['recordsTotal'] = 0;}
        $result['recordsFiltered'] = $user_filtered;

        return $result;
    }
}
