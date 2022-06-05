<?php
class smUserModel extends waModel
{
    protected $table = 'sm_user';
	
	public function getActiveById($id)
	{
		$data = $this->query("SELECT * FROM ".$this->table." WHERE id = i:id AND deleted = 0", array('id' => $id))->fetchAll();
		if(!count($data)) {return null;}
		return $data[0];
	}
	
	public function listUsersData($start, $length, $order, $column, $direction, $search)
	{
		$contact_model = new waContactModel();
		
		$order_by = array(
			0 => 'A.id',
			1 => 'B.name',
		);
		
		// Вычисление общего количества записей
		$rec_total = $this->query("SELECT COUNT(id) AS total FROM ".$this->table)->fetchAll();
		$rec_total = $rec_total[0]['total'];
		
		$contact_model = new waContactModel();
		// Базовый запрос
		$query = "SELECT *, B.name FROM ".$this->table." AS A LEFT JOIN ".$contact_model->getTableName()." AS B ON A.id = B.id";
		// Поиск
		if($search)
		{
			$search_query = " WHERE A.id = ".intval($search)." OR B.name LIKE '%".$this->escape($search)."%'";
			$query .= $search_query;
		}
		// Сортировка
		if($order) 
		{
			if(isset($order_by[$column])) {$query.=" ORDER BY ".$order_by[$column]." ".$direction;}
			else {$query.=" ORDER BY ".$order_by[0]." ".$direction;}
		}
		$query_no_limit = $query;
		
		// Количество
		$query.=" LIMIT ".intval($start).",".intval($length);
		
		// Выполнение поиска
		$users = $this->query($query)->fetchAll();
		$user_filtered = $this->query($query_no_limit)->count();
		
		$result = array();
		$result['data'] = array();
		if($user_filtered > 0)
		{
			foreach($users as $user)
			{
				$data = array(
					intval($user['id']),
					'<a href="?module=user&id='.$user['id'].'">'.htmlspecialchars($user['name'], ENT_QUOTES).'</a>',
				);
				array_push($result['data'], $data);
			}
		}
		
		$result['recordsTotal'] = $rec_total;
		if(!$result['recordsTotal']) {$result['recordsTotal'] = 0;}
		$result['recordsFiltered'] = $user_filtered;
		
		return $result;
	}

	public function getWithValidTariff($tariff_id)
	{
		return $this->query("SELECT * FROM ".$this->table." WHERE tariff_id = s:tariff_id AND subscribtion_valid_till > s:date",
								array('tariff_id' => $tariff_id, 'date' => date('Y-m-d H:i:s')))->fetchAll('id');
	}
	
	public function massUpgrade($old_tariff_id, $new_tariff_id)
	{
		$this->query("UPDATE ".$this->table." SET tariff_id = s:new_tariff_id
						WHERE tariff_id = s:old_tariff_id AND subscribtion_valid_till > s:date",
						array('new_tariff_id' => $new_tariff_id, 'old_tariff_id' => $old_tariff_id, 'date' => date('Y-m-d H:i:s')));
	}

    public function listEntities($start, $length, $order, $column, $direction, $search)
    {
        $order_by = array(
            0 => 'su.id',
            1 => 'wc.name',
        );

        // Вычисление общего количества записей
        $rec_total = $this->query("SELECT COUNT(su.id) AS total 
                                        FROM ".$this->table." as su 
                                        LEFT JOIN wa_contact as wc 
                                        ON su.id = wc.id ")->fetchAll();
        $rec_total = $rec_total[0]['total'];

        // Базовый запрос
        $query = "SELECT *, wc.name AS name, st.name AS tariff_name, su.id as id
                  FROM ".$this->table." as su 
                  LEFT JOIN wa_contact as wc ON su.id = wc.id
                  LEFT JOIN wa_contact_emails as wce ON wc.id = wce.contact_id
                  LEFT JOIN sm_fluser_smuser as sfs ON sfs.sm_user_id = su.id
                  LEFT JOIN sm_tariff as st ON su.tariff_id = st.id
                  LEFT JOIN sm_contact_referral as scr ON su.id = scr.contact_id
                  ";

        // Поиск
        if($search)
        {
            $query = $query." WHERE (su.id = ".intval($search)." OR wc.name LIKE ('%".$this->escape($search)."%'))";
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
        $users = $this->query($query)->fetchAll();
        $user_filtered = $this->query($query_no_limit)->count();

        $result = array();
        $result['data'] = array();

        if($user_filtered > 0)
        {
            foreach($users as $user)
            {
                array_push($result['data'], array(
                    $user['id'],
                    '<a href="?module=clients&action=edit&id='.$user['id'].'">'.$user['name'].'</a>',
                    htmlspecialchars($user['tariff_name'], ENT_QUOTES),
                    htmlspecialchars($user['subscribtion_valid_till'], ENT_QUOTES),
                    htmlspecialchars($user['balance'], ENT_QUOTES),
                    htmlspecialchars($user['fl_user_id'], ENT_QUOTES),
                    htmlspecialchars($user['fl_token'], ENT_QUOTES),
                    htmlspecialchars($user['email'],ENT_QUOTES),
                    htmlspecialchars($user['referral_code'],ENT_QUOTES),
                ));
            }
        }

        $result['recordsTotal'] = $rec_total;
        if(!$result['recordsTotal']) {$result['recordsTotal'] = 0;}
        $result['recordsFiltered'] = $user_filtered;

        return $result;
    }

    public function getUsersWithEndedSubscription() {
	    return $this->query("SELECT * FROM {$this->table} WHERE subscribtion_valid_till < NOW()")->fetchAll();
    }
}
