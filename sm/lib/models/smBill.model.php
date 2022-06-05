<?php

class smBillModel extends waModel
{
    protected $table = 'sm_bill';
	
	public function createBill($order_id, $user_data, $host_data)
	{
		$data = array(
			'order_id' => $order_id,
			'number' => 'C'.str_pad($order_id, 5, '0', STR_PAD_LEFT),
			'date' => date('Y-m-d'),
		);
		foreach($user_data as $key => $value)
		{
			$data['user_'.$key] = $value;
		}
		foreach($host_data as $key => $value)
		{
			$data['host_'.$key] = $value;
		}
		return $this->insert($data);
	}
}