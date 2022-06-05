<?php

class smFrontendPaymentResultAction extends waViewAction
{
    public function execute()
    {
		$this->setLayout(new smFrontendLayout());
		
		$user = new smUser();
		if(!$user->isUser())
		{
			$this->showError(_w("Page not found"),  _w("Oops .. Page not found."), _w("Something went wrong and we can't find this page"), 0);
			return;
		}
		
		$order_id = waRequest::get('order_id', 0, 'int');
		$order_model = new smOrderModel();
		$order_data = $order_model->getById($order_id);
		if(!$order_data)
		{
			$this->showError(_w("Page not found"),  _w("Oops .. Page not found."), _w("Something went wrong and we can't find this page"), 0);
			return;
		}
		
		if($user->getId() != $order_data['contact_id'])
		{
			$this->showError(_w("Page not found"),  _w("Oops .. Page not found."), _w("Something went wrong and we can't find this page"), 0);
			return;
		}
		
		$status = $order_data['status'];
		$statuses = smOrderHelper::getOrderStatuses();
		if(!isset($statuses[$status]))
		{
			$this->showError(_w("Page not found"),  _w("Oops .. Page not found."), _w("Something went wrong and we can't find this page"), 0);
			return;
		}
		
		$this->setThemeTemplate('payment-result.html');
		
		$status_info = $statuses[$status];
		$this->view->assign('status_info', $status_info);
		
		if($status_info['is_paid']) {wa()->getResponse()->setTitle(_w("Successful payment - Smotron"));}
		else {wa()->getResponse()->setTitle(_w("Payment is not received - Smotron"));}
		
		$this->view->assign('order_data', $order_data);
	}
	
	protected function showError($title, $h1, $error, $no_disclaimer = 1)
	{
		wa()->getResponse()->setTitle($title);
		wa()->getResponse()->setStatus(404);
		$this->setThemeTemplate('error.html');
		$this->view->assign('error_title', $h1);
		$this->view->assign('error', $error);
		if($no_disclaimer)
		{
			$this->view->assign('error_no_disclaimer', 1);
		}
	}
}
