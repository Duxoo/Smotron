<?php

class smFrontendOrderAction extends waViewAction
{
    public function execute()
    {
		$this->setLayout(new smFrontendLayout());

		$month_count = intval(waRequest::get('month_count',1,'int'));
        $extend = waRequest::get('extend', 0, 'int');
        $change = waRequest::get('change', 0, 'int');
		$tariff_id = waRequest::get('tariff', '', 'string');
		$payment_id = waRequest::get('payment', 0, 'int');
		$type = waRequest::get('type', 'subscribe', 'string');
		
		$order_result = smOrderHelper::createOrder(wa()->getUser()->getId(), $type, $tariff_id, $payment_id, $month_count, $extend, $change);
		if($order_result['result'] == 0)
		{
			$this->showError(_w("Error checkout Smotron"),  _w("An error occurred"), $order_result['message']);
			return;
		}
		if($order_result['instant_payment'] == 1)
		{
			wa()->getResponse()->redirect(wa()->getRouteUrl('sm/frontend/paymentResult').'?order_id='.$order_result['order_id'], 302);
			wa()->getResponse()->setTitle(_w("Technical forwarding Smotron"));
			$this->setThemeTemplate('order.html');
			return;
		}
		
		wa()->getResponse()->setTitle(_w("Order payment"));
		$this->setThemeTemplate('order.html');
		
		$order_model = new smOrderModel();
		$order_data = $order_model->getById($order_result['order_id']);
		$order_data['description'] = smOrderHelper::getOrderDescription($order_data, $month_count);
		
		try
		{
			if($order_data['payment_id'] != -1)
			{
				$plugin = smPayment::getPlugin(null, $order_data['payment_id']);
				$payment = $plugin->payment(array(), $order_data, true);
			}
			else
			{
				$bill_id = smBillHelper::createBill($order_data);
				if(!$bill_id) {$payment = _w("Error generating an invoice");}
				else
				{
					$payment = null;
					$this->view->assign('bill_id', $bill_id);
				}
			}
		}
		catch (waException $ex)
		{
			$this->showError(_w("Error checkout Smotron"),  _w("An error occurred"), $ex->getMessage());
			return;
		}
		$this->view->assign('payment', $payment);
		$this->view->assign('payment_id', $order_data['payment_id']);
	}
	
	protected function showError($title, $h1, $error)
	{
		wa()->getResponse()->setTitle($title);
		wa()->getResponse()->setStatus(404);
		$this->setThemeTemplate('error.html');
		$this->view->assign('error_title', $h1);
		$this->view->assign('error', $error);
		$this->view->assign('error_no_disclaimer', 1);
		
		$breadcrumbs = array(
			0 => array(
				'url' => wa()->getRouteUrl('sm/frontend/checkout'),
				'name' => _w("Purchase a subscription")
			)
		);
		$this->view->assign('breadcrumbs', $breadcrumbs);
		
		$error_link = array(
			'url' => wa()->getRouteUrl('sm/frontend/checkout'),
			'text' => _w("Return to the purchase page")
		);
		$this->view->assign('error_link', $error_link);
	}
}
