<?php

class smTariffModel extends waModel
{
    protected $table = 'sm_tariff';

    public function listEntities($start, $length, $order, $column, $direction, $search)
    {
        $order_by = array(
            0 => 'id',
            1 => 'name',
            2 => 'base_range',
            3 => 'custom_range',
            4 => 'sort',
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
        $tariffs = $this->query($query)->fetchAll();
        $user_filtered = $this->query($query_no_limit)->count();

        $result = array();
        $result['data'] = $tariffs;

        $result['recordsTotal'] = $rec_total;
        if(!$result['recordsTotal']) {$result['recordsTotal'] = 0;}
        $result['recordsFiltered'] = $user_filtered;

        return $result;
    }

    public function getTariffs($sort = true, $asc = true)
    {
        if(isset($asc))
        {
            if($asc === true) { $asc = 'ASC'; }
            else { $asc = 'DESC'; }
        }
        $query = "SELECT * FROM ".$this->table." WHERE hidden = 0";
        if(isset($sort) & $sort === true){ $query .=" ORDER BY `sort` ".$asc; }
        return $this->query($query)->fetchAll();
    }

    public function getPublicTariffs($sort = true, $asc = true)
    {
        if(isset($asc))
        {
            if($asc === true) { $asc = 'ASC'; }
            else { $asc = 'DESC'; }
        }
        $query = "SELECT * FROM ".$this->table." WHERE hidden = 0 AND is_custom = 0";
        if(isset($sort) & $sort === true){$query .=" ORDER BY 'sort' ".$asc;}
        return $this->query($query)->fetchAll();
    }

	public function getAllSorted()
	{
		return $this->query("SELECT * FROM ".$this->table." ORDER BY sort ASC")->fetchAll('id');
	}
	
	public function getByUser($user_id)
	{
		return $this->query("SELECT * FROM ".$this->table." WHERE (is_custom = 0 OR (is_custom = 1 AND user_id = i:user)) AND hidden = 0 ORDER BY sort ASC",
								array('user' => $user_id))->fetchAll('id');
	}

    public function getByUrl($url)
    {
        return $this->query("SELECT * FROM ".$this->table." WHERE url = s:url AND hidden = 0 ORDER BY sort ASC",
                                array('url' => $url))->fetchAll();
    }
}