<?php
class smOrderModel extends waModel
{
    protected $table = 'sm_order';
	
	public function create($contact_id, $type, $count, $tariff_id, $tariff_name, $payment_id, $payment_plugin, $total, $is_first)
	{
		return $this->insert(array(
			'contact_id' => $contact_id,
			'type' => $type,
			'count' => $count,
			'tariff_id' => $tariff_id,
			'tariff_name' => $tariff_name,
			'payment_id' => $payment_id,
			'payment_plugin' => $payment_plugin,
			'total' => $total,
			'create_datetime' => date('Y-m-d H:i:s'),
			'create_date' => date('Y-m-d'),
			'is_first' => $is_first,
		));
	}
	
	public function setPaid($order_id, $paid_datetime = null)
	{
		if(!$this->getById($order_id)) {return;}
		
		if($paid_datetime === null) {$paid_datetime = date('Y-m-d H:i:s');}
		$paid_date = mb_substr($paid_datetime, 0, 10);
		
		$this->updateById($order_id, array(
			'paid_datetime' => $paid_datetime,
			'paid_date' => $paid_date,
		));
	}
	
	public function setStatus($order_id, $status)
	{
		if(!$this->getById($order_id)) {return;}
		$this->updateById($order_id, array(
			'status' => $status,
		));
	}
	
	public function countRealMoneyPaidOrders($user_id)
	{
		$counter = $this->query("SELECT COUNT(id) AS cnt FROM ".$this->table."
									WHERE contact_id = i:user_id AND payment_id != 0 AND paid_date IS NOT NULL", array('user_id' => $user_id))->fetchAll();
		if(!count($counter)) {return 0;}
		return $counter[0]['cnt'];
	}
	
	public function countPaidOrders($user_id)
	{
		$counter = $this->query("SELECT COUNT(id) AS cnt FROM ".$this->table."
									WHERE contact_id = i:user_id AND paid_date IS NOT NULL", array('user_id' => $user_id))->fetchAll();
		if(!count($counter)) {return 0;}
		return $counter[0]['cnt'];
	}
	
	public function getSubsequentOrders($order_id, $type, $status)
	{
		$order_data = $this->getById($order_id);
		if(!$order_data) {return array();}
		$user_id = $order_data['contact_id'];
		
		return $this->query("SELECT * FROM ".$this->table." WHERE id > i:id AND contact_id = i:user AND type = s:type AND status = s:status",
								array('id' => $order_id, 'user' => $user_id, 'type' => $type, 'status' => $status))->fetchAll('id');
	}

	public function countByUser($user_id)
	{
		$cnt = $this->query("SELECT COUNT(id) AS cnt FROM ".$this->table." WHERE contact_id = i:user", array('user' => $user_id))->fetchAll();
		if(!count($cnt)) {return 0;}
		return $cnt[0]['cnt'];
	}
	
	public function outdateUpgrades($user_id)
	{
		$this->query("UPDATE ".$this->table." SET is_outdated = 1 WHERE contact_id = i:user AND type = 'upgrade'", array('user' => $user_id));
	}

    public function getMinDate()
    {
        $cache = new waSerializeCache('sm_order_min_date');
        if ($cache->isCached()) {
            return $cache->get();
        }
        $result = $this->query("SELECT MIN(create_datetime)
                                FROM sm_order
                                WHERE create_datetime > '0000-00-00 00:00:00'")->fetchField();
        if ($result) {
            $cache->set($result);
            return $result;
        } else {
            return date('Y-m-01', strtotime("-1 months"));
        }
    }
}
