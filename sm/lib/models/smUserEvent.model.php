<?php
class smUserEventModel extends waModel
{
    protected $table = 'sm_user_event';
	
	public function getEventTypes()
	{
		return array(
			'gift' => array(
				'name' => 'Подарок',
				'color' => '#ba7200',
			),
			'promo' => array(
				'name' => 'Промокод',
				'color' => '#3c7400',
			),
			'refund' => array(
				'name' => 'Возврат',
				'color' => '#ff0000',
			),
			'auto_upgrade' => array(
				'name' => 'Автоапгрейд',
				'color' => '#0009bc',
			),
		);
	}
	
	public function addEvent($user_id, $type, $comment, $emit_id, $entity_id = 0)
	{
		return $this->insert(array(
			'user_id' => $user_id,
			'type' => $type,
			'comment' => $comment,
			'emit_id' => $emit_id,
			'entity_id' => $entity_id,
			'register_datetime' => date('Y-m-d H:i:s')
		));
	}

    public function updateEventComment($event_id, $comment)
    {
        return $this->updateById($event_id, array(
            'comment' => $comment,
        ));
    }
	
	public function getBackendHistory($start, $length, $order, $column, $direction, $search, $user_id)
	{
		$order_by = array(
			0 => 'A.id',
			1 => 'A.type',
			2 => 'A.comment',
			3 => 'B.name',
			4 => 'A.register_datetime',
		);
		
		// Вычисление общего количества записей
		$rec_total = $this->query("SELECT COUNT(id) AS total FROM ".$this->table."
									WHERE user_id = i:user", array('user' => $user_id))->fetchAll();
		$rec_total = $rec_total[0]['total'];
		
		$contact_model = new waContactModel();
		// Базовый запрос
		$query = "SELECT A.*, B.name FROM ".$this->table." AS A
					LEFT JOIN ".$contact_model->getTableName()." AS B
					ON A.emit_id = B.id
					WHERE A.user_id = ".intval($user_id);
		// Поиск
		if($search)
		{
			$search_query = " AND (DATE_FORMAT(A.register_datetime, '%d.%m.%Y') LIKE '%".$this->escape($search)."%' 
								OR A.type LIKE '%".$this->escape($search)."%' OR A.comment LIKE '%".$this->escape($search)."%'";
			$query .= $search_query.")";
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
		$events = $this->query($query)->fetchAll();
		$user_filtered = $this->query($query_no_limit)->count();
		
		$result = array();
		$result['data'] = array();
		if($user_filtered > 0)
		{
			$types = $this->getEventTypes();
			foreach($events as $event)
			{
				$type = $event['type'];
				if(isset($types[$type])) {$type = '<span style="color:'.$types[$type]['color'].';">'.$types[$type]['name'].'</span>';}
				
				$name = 'Система';
				if($event['name']) {$name = htmlspecialchars($event['name'], ENT_QUOTES).' (ID: '.$event['emit_id'].')';}
				
				$data = array(
					'#'.$event['id'],
					$type,
					htmlspecialchars($event['comment'], ENT_QUOTES),
					$name,
					date('d.m.Y H:i:s', strtotime($event['register_datetime'])),
				);
				array_push($result['data'], $data);
			}
		}
		
		$result['recordsTotal'] = $rec_total;
		if(!$result['recordsTotal']) {$result['recordsTotal'] = 0;}
		$result['recordsFiltered'] = $user_filtered;
		
		return $result;
	}

	public function listByType($start, $length, $order, $column, $direction, $search, $type)
	{
		$order_by = array(
			0 => 'A.id',
			1 => 'B.name',
			2 => 'A.comment',
			3 => 'A.register_datetime',
		);
		
		// Вычисление общего количества записей
		$rec_total = $this->query("SELECT COUNT(id) AS total FROM ".$this->table."
									WHERE type = s:type", array('type' => $type))->fetchAll();
		$rec_total = $rec_total[0]['total'];
		
		$contact_model = new waContactModel();
		// Базовый запрос
		$query = "SELECT A.*, B.name FROM ".$this->table." AS A
					LEFT JOIN ".$contact_model->getTableName()." AS B
					ON A.user_id = B.id
					WHERE A.type = '".$this->escape($type)."'";
		// Поиск
		if($search)
		{
			$search_query = " AND (DATE_FORMAT(A.register_datetime, '%d.%m.%Y') LIKE '%".$this->escape($search)."%' 
								OR A.comment LIKE '%".$this->escape($search)."%'
								OR B.name LIKE '%".$this->escape($search)."%'
								OR B.id = ".intval($search);
			$query .= $search_query.")";
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
		$events = $this->query($query)->fetchAll();
		$user_filtered = $this->query($query_no_limit)->count();
		
		$result = array();
		$result['data'] = array();
		if($user_filtered > 0)
		{
			foreach($events as $event)
			{
				$name = 'Система';
				if($event['name'])
				{
					$name = '<a href="?module=user&id='.intval($event['user_id']).'">'.htmlspecialchars($event['name'], ENT_QUOTES).' (ID: '.intval($event['user_id']).')</a>';
				}
				
				$data = array(
					'#'.$event['id'],
					$name,
					htmlspecialchars($event['comment'], ENT_QUOTES),
					date('d.m.Y H:i:s', strtotime($event['register_datetime'])),
				);
				array_push($result['data'], $data);
			}
		}
		
		$result['recordsTotal'] = $rec_total;
		if(!$result['recordsTotal']) {$result['recordsTotal'] = 0;}
		$result['recordsFiltered'] = $user_filtered;
		
		return $result;
	}
}
