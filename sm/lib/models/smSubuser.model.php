<?php

class smSubuserModel extends waModel
{
    protected $table = 'sm_contact_subusers';

    public function listEntities($start, $length, $order, $column, $direction, $search, $parent_contact_id = null)
    {
        $order_by = array(
            0 => 'id',
            1 => 'name',
        );

        // Вычисление общего количества записей
        $rec_total = $this->query("SELECT COUNT(id) AS total FROM ".$this->table." WHERE dissable = 0 AND parent_contact_id = ".$parent_contact_id)->fetchAll();
        $rec_total = $rec_total[0]['total'];

        // Базовый запрос
        $query = "SELECT * FROM ".$this->table." WHERE dissable = 0 AND parent_contact_id = ".$parent_contact_id;

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
        $subusers = $this->query($query)->fetchAll();
        $user_filtered = $this->query($query_no_limit)->count();

        $result = array();
        $result['data'] = array();
        
        if($user_filtered > 0)
        {
            foreach($subusers as $subuser)
            {
                $url = "subuser/".$subuser['id']."/delete/";
                array_push($result['data'], array(
                    htmlspecialchars($subuser['name'], ENT_QUOTES),
                    htmlspecialchars($subuser['login'], ENT_QUOTES),
                    '<a class="icon edit dashboard ps-icon edit" href="subuser/'.$subuser['id'].'/"></a>',
                    '<a class="icon delete2" data-id="'.$subuser['id'].'" data-href="subuser/'.$subuser['id'].'/delete/" onclick="deleteSubuser(this,'.$subuser['id'].')"><i class="icon delete" alt="удалить"></i></a>',
                    //'<a href="?module=municipality&id='.$municipality['id'].'">'.htmlspecialchars($municipality['name'], ENT_QUOTES).'</a>',
                ));
            }
        }

        $result['recordsTotal'] = $rec_total;
        if(!$result['recordsTotal']) {$result['recordsTotal'] = 0;}
        $result['recordsFiltered'] = $user_filtered;

        return $result;
    }
}
