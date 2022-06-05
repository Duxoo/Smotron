<?php
class smUserBalanceHistoryModel extends waModel
{
    protected $table = 'sm_user_balance_history';
	
	public function getTransactionTypes()
	{
		return array(
			'manual' => array(
				'name' => 'Вручную',
				'color' => '#ef8d1a',
			),
			'refund' => array(
				'name' => 'Возврат',
				'color' => '#ff0000',
			),
			'payout' => array(
				'name' => 'Вывод средств',
				'color' => '#6f8700',
			),
			'referal' => array(
				'name' => 'Реферал',
				'color' => '#7200ba',
			),
			'referal_refund' => array(
				'name' => 'Реферал',
				'color' => '#7200ba',
			)
		);
	}
	
	public function getBackendHistory($start, $length, $order, $column, $direction, $search, $user_id)
	{
		$order_by = array(
			0 => 'A.id',
			1 => 'A.type',
			2 => 'A.amount',
			3 => 'A.amount_before',
			4 => 'A.amount_after',
			5 => 'A.type',
			6 => 'A.comment',
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
			$search_query = " AND (DATE_FORMAT(A.transaction_datetime, '%d.%m.%Y') LIKE '%".$this->escape($search)."%'";
			if(is_numeric($search))
			{
				$search_query .= " OR A.amount_before = ".str_replace(",", ".", floatval($search))."
								   OR A.amount_after = ".str_replace(",", ".", floatval($search))."
								   OR A.amount = ".str_replace(",", ".", floatval($search));
			}
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
		$transactions = $this->query($query)->fetchAll();
		$user_filtered = $this->query($query_no_limit)->count();
		
		$result = array();
		$result['data'] = array();
		if($user_filtered > 0)
		{
			$types = $this->getTransactionTypes();
			foreach($transactions as $transaction)
			{
				$amount_literal = '<span class="sm-green bold">'.wa_currency($transaction['amount'], '', '%2i').'</span>';
				if($transaction['amount'] < 0) {$amount_literal = '<span class="sm-red bold">'.wa_currency($transaction['amount'], '', '%2i').'</span>';}

				$amount_after_literal = '<span class="sm-green bold">'.wa_currency($transaction['amount_after'], '', '%2i').'</span>';
				if($transaction['amount_after'] < 0) {$amount_after_literal = '<span class="sm-red bold">'.wa_currency($transaction['amount_after'], '', '%2i').'</span>';}
				
				if($transaction['emit_id']) {$transaction['comment'] .= ' - инициатор '.ifempty($transaction['name'], '').' ID: '.$transaction['emit_id'];}
				$transaction['comment'] = htmlspecialchars($transaction['comment'], ENT_QUOTES);
				
				$type = $transaction['type'];
				if(isset($types[$type])) {$type = '<span style="color:'.$types[$type]['color'].';">'.$types[$type]['name'].'</span>';}
				
				$data = array(
					'#'.$transaction['id'],
					$type,
					'<div style="white-space: nowrap;">'.$amount_literal.'</div>',
					'<div style="white-space: nowrap;">'.wa_currency($transaction['amount_before'], '', '%2i').' B</div>',
					'<div style="white-space: nowrap;">'.wa_currency($transaction['amount_after'], '', '%2i').' B</div>',
					'<span class="history-comment">'.$transaction['comment'].'</span>',
					date('d.m.Y H:i:s', strtotime($transaction['transaction_datetime'])),
				);
				array_push($result['data'], $data);
			}
		}
		
		$result['recordsTotal'] = $rec_total;
		if(!$result['recordsTotal']) {$result['recordsTotal'] = 0;}
		$result['recordsFiltered'] = $user_filtered;
		
		return $result;
	}
	
	public function getFrontendHistory($start, $length, $order, $column, $direction, $search, $user_id)
	{
		$order_by = array(
			0 => 'transaction_datetime',
			1 => 'amount',
			3 => 'comment',
		);
		
		// Вычисление общего количества записей
		$rec_total = $this->query("SELECT COUNT(id) AS total FROM ".$this->table."
									WHERE user_id = i:user", array('user' => $user_id))->fetchAll();
		$rec_total = $rec_total[0]['total'];
		
		$contact_model = new waContactModel();
		// Базовый запрос
		$query = "SELECT * FROM ".$this->table." WHERE user_id = ".intval($user_id);
		// Поиск
		if($search)
		{
			$search_query = " AND (DATE_FORMAT(transaction_datetime, '%d.%m.%Y') LIKE '%".$this->escape($search)."%' OR comment LIKE '%".$this->escape($search)."%'";
			if(is_numeric($search))
			{
				/*$search_query .= " OR amount = ".str_replace(",", ".", floatval($search));*/
                $search_query .= " OR amount LIKE '%".str_replace(",", ".", floatval($search))."%'";
			}
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
		$transactions = $this->query($query)->fetchAll();
		$user_filtered = $this->query($query_no_limit)->count();
		
		$result = array();
		$result['data'] = array();
		if($user_filtered > 0)
		{
			$user_timezone = smTimezoneHelper::timezone();
			foreach($transactions as $transaction)
			{
				$amount_literal = '<span class="sm-green bold">'.wa_currency($transaction['amount'], '', '%2i').'</span>';
				if($transaction['amount'] < 0) {$amount_literal = '<span class="sm-red bold">'.wa_currency($transaction['amount'], '', '%2i').'</span>';}
				
				$datetime = smTimezoneHelper::convertTimeZone($transaction['transaction_datetime'], 'Europe/Moscow', $user_timezone);
				
				$data = array(
					date('d.m.Y H:i:s', strtotime($datetime)),
					'<div style="white-space: nowrap;">'.$amount_literal.'</div>',
					'<span class="history-comment">'.htmlspecialchars($transaction['comment'], ENT_QUOTES).'</span>'
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
