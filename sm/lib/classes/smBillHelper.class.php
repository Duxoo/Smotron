<?php

class smBillHelper
{
    static public function getBillFields()
	{
		return array(
			'company' => array(
				'name' => 'Компания',
			),
			'inn' => array(
				'name' => 'ИНН',
				'dadata' => 'inn',
				'class' => 'dadata-company',
			),
			'ogrn' => array(
				'name' => 'ОГРН/ОГРНИП',
				'dadata' => 'ogrn',
				'class' => 'dadata-company',
			),
			'okpo' => array(
				'name' => 'ОКПО',
				'dadata' => 'okpo',
				'class' => 'dadata-company',
				'required' => 0,
			),
			'address' => array(
				'name' => 'Юридический адрес',
			),
			'zip' => array(
				'name' => 'Индекс',
				'dadata' => 'postal_code',
				'class' => 'dadata-address',
			),
			'bank' => array(
				'name' => 'Банк'
			),
			'rs' => array(
				'name' => 'Расчетный счет'
			),
			'ks' => array(
				'name' => 'Корсчет'
			),
			'bik' => array(
				'name' => 'БИК банка',
				'dadata' => 'bic',
				'class' => 'dadata-bank',
			),
			'kpp' => array(
				'name' => 'КПП банка',
				'dadata' => 'kpp',
				'class' => 'dadata-bank',
			),
		);
	}

	static public function getFilledBillFields()
	{
		$fields = self::getBillFields();
		$data = null;
		if(wa()->getUser()->isAuth())
		{
			$contact_id = wa()->getUser()->getId();
			$user_company_model = new smUserCompanyModel();
			$data = $user_company_model->getById($contact_id);
		}
		if(!$data) {$data = array();}
		
		foreach($fields as $key => $field)
		{
			$fields[$key]['value'] = ifempty($data[$key], '');
		}
		
		return $fields;
	}
	
	static public function getFaximileDir()
	{
		return wa()->getDataPath('faximile', false, 'sm');
	}
	
	static public function getFaximilePath()
	{
		$settings_model = new smSettingsModel();
		$faximile_name = $settings_model->getParam('faximile_file_name', 'string', null);
		if(!$faximile_name) {return null;}
		return wa()->getDataPath('faximile', false, 'sm').'/'.$faximile_name;
	}
	
	static public function getFaximileUrl()
	{
		$settings_model = new smSettingsModel();
		$faximile_name = $settings_model->getParam('faximile_file_name', 'string', null);
		if(!$faximile_name) {return null;}
		return wa()->getDataUrl('faximile', false, 'sm', true).$faximile_name;
	}
	
	static public function isBillFilled($user_id)
	{
		$user_company_model = new smUserCompanyModel();
		$data = $user_company_model->getById($user_id);
		if(!$data) {return 0;}
		
		$fields = self::getBillFields();
		foreach($fields as $key => $value)
		{
			$required = 1;
			if(isset($value['required'])) {$required = $value['required'];}
			if($required && empty($data[$key])) {return 0;}
		}
		
		return 1;
	}

	static public function createBill($order_data)
	{
		$bill_fields = self::getBillFields();
		
		$user_id = $order_data['contact_id'];
		$user_company_model = new smUserCompanyModel();
		$user_data = $user_company_model->getById($user_id);
		if(!$user_data) {$user_data = array();}
		if(isset($user_data['id'])) {unset($user_data['id']);}
		
		$settings_model = new smSettingsModel();
		$settings = $settings_model->getSettings();
		$host_data = array();
		foreach($bill_fields as $key => $value)
		{
			if(isset($settings['bill_'.$key]))
			{
				$host_data[$key] = $settings['bill_'.$key];
			}
		}
		$host_data['director'] = ifempty($settings['bill_director'], '');
		$host_data['buh'] = ifempty($settings['bill_buh'], '');
		
		$bill_model = new smBillModel();
		$bill_id = $bill_model->createBill($order_data['id'], $user_data, $host_data);
		
		return $bill_id;
	}

	static public function getBillDir()
	{
		return wa()->getDataPath('bills', false, 'sm');
	}

	static public function generateBill($bill_id)
	{
		$bill_model = new smBillModel();
		$bill_data = $bill_model->getById($bill_id);
		if(!$bill_data) {return null;}
		$bill_path = self::getBillDir().'/'.$bill_id.'.pdf';
		
		$order_id = $bill_data['order_id'];
		$order_model = new smOrderModel();
		$order_data = $order_model->getById($order_id);
		if(!$order_data) {return null;}
		
		$view = wa()->getView();
		$view->assign('data', $bill_data);
		$view->assign('order', $order_data);
		$html = $view->fetch(wa()->getAppPath(null, 'sm').'/templates/docs/Bill.html');
		
		if(!class_exists('TCPDF')) {require_once wa()->getAppPath('dist/tcpdf/tcpdf.php', 'sm');}
		$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetFont('dejavusans','', 8);
		$pdf->setPrintHeader(false);
		$pdf->AddPage();
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('smotron.tv');
		$pdf->SetTitle('Счет на оплату №'.$bill_data['number']);
		$pdf->writeHTML($html, true, false, true, false, '');
		if($pdf->GetY() > 250) {$pdf->AddPage();}
		$pdf->Image(self::getFaximilePath(), 90, $pdf->GetY()-24, 40, 40);
		$pdf->Output($bill_path, 'F');
		return $bill_path;
	}
}