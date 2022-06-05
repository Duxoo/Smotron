<?php

class smFrontendBillAction extends waViewAction
{
    public function execute()
    {
		if(!wa()->getUser()->isAuth())
		{
			$this->showError(_w("404. Page not found"));
			return;
		}
		
		$bill_id = waRequest::param('bill_id', 0, 'int');
		$bill_model = new smBillModel();
		$bill_data = $bill_model->getById($bill_id);
		if(!$bill_data)
		{
			$this->showError(_w("404. Page not found"));
			return;
		}
		
		$order_id = $bill_data['order_id'];
		$order_model = new smOrderModel();
		$order_data = $order_model->getById($order_id);
		if(!$order_data)
		{
			$this->showError(_w("404. Page not found"));
			return;
		}
		
		if($order_data['contact_id'] != wa()->getUser()->getId())
		{
			$this->showError(_w("404. Page not found"));
			return;
		}
		
		$bill_path = smBillHelper::generateBill($bill_id);
		if(!$bill_path)
		{
			$this->showError(_w("404. Page not found"));
			return;
		}
		
		waFiles::readFile($bill_path);//, 'Счет на оплату №'.$bill_data['number'].'.pdf');
		
		$this->setThemeTemplate('blank.html');
	}
	
	protected function showError($title)
	{
		$this->setLayout(new smFrontendLayout());
		wa()->getResponse()->setTitle($title);
		wa()->getResponse()->setStatus(404);
		$this->setThemeTemplate('error.html');
	}
}
