<?php

class smFrontendCheckoutAction extends waViewAction
{
    public function execute()
    {
		$this->setLayout(new smFrontendLayout());
		
		$user = new smUser();
		if(!$user->isUser())
		{
			wa()->getResponse()->setTitle(_w("sign in"));
			wa()->getResponse()->setStatus(404);
			$this->setThemeTemplate('error.html');
			$this->view->assign('error_title', _w("Log in or register"));
			$this->view->assign('error', _w("To make purchases, you need to log in or register"));
			$this->view->assign('error_no_disclaimer', 1);
			$this->view->assign('error_button', array('url' => '#', 'text' => _w("register"), 'class' => 'sm-auth-trigger sm-button-white'));
			return;
		}
		
		$this->setThemeTemplate('checkout.html');
		$user_id = $user->getId();
		$tariffs = smTariff::getUserTariffs($user_id);
        $tariff_channels_model = new smTariffChannelsModel();
        foreach ($tariffs as $key => $tariff)
        {
            $tariffs[$key]['channels'] = $tariff_channels_model->getChannelsByTariff($tariff['id']);
        }
		$user_data = $user->getData();

        $preferred = waRequest::get('tariff_id', '', 'int');
        $extend = waRequest::get('extend', null);
        $change = waRequest::get('change', null);
		if(!$user_data['subscribtion_valid'])
		{
			wa()->getResponse()->setTitle(_w("To purchase a subscription - Smotron"));
			$this->view->assign('tariffs', $tariffs);
			$this->view->assign('checkout_mode', 'subscribe');
		}
		elseif($extend == 1)
        {
            wa()->getResponse()->setTitle(_w("Renewal - Smotron"));
            $this->view->assign('tariffs', array($preferred => $tariffs[$preferred]));
            $this->view->assign('extend', true);
            $this->view->assign('checkout_mode', 'subscribe');
        }
        elseif($change == 1)
        {
            wa()->getResponse()->setTitle(_w("Change of membership - Smotron"));
            $user_data = $user->getData();
            $tariffs_up = smSubscribtionHelper::getUpgradeOptions(
                $user_data['tariff_id'],
                $user_data['subscribtion_cost_actual'],
                $user_data['subscribtion_activated'],
                $user_data['subscribtion_valid_till']
            );
            //$tariffs = array();
            foreach ($tariffs_up as $key => $tariff)
            {
                //$tariffs[$key] = $tariff;
                $tariffs[$key]['cost'] = $tariff['cost'];
                $tariffs[$key]['price'] = $tariffs[$key]['cost'];
            }
            //$this->view->assign('current_tariff_amount', $change_data);
            $this->view->assign('tariffs', $tariffs);
            $this->view->assign('change', true);
            $this->view->assign('checkout_mode', 'subscribe');
        }
		else
		{
			wa()->getResponse()->setTitle(_w("You already have a subscription"));
			wa()->getResponse()->setStatus(404);
			$this->setThemeTemplate('error.html');
			$this->view->assign('error_title', _w("Your subscription is still valid"));
			$this->view->assign('error', _w("To purchase a new subscription, you must first cancel the old one"));
			$this->view->assign('error_no_disclaimer', 1);
			$this->view->assign('error_button', array('url' => wa()->getRouteUrl('sm/frontend/myTariff'), 'text' => _w("Subscription management"), 'class' => ''));
			return;
		}

        //$preferred = waRequest::get('tariff_id', '', 'int');
		if(!isset($tariffs[$preferred]) && count($tariffs))
		{
			foreach($tariffs as $key => $tariff) {$preferred = $tariff['id']; break;}
		}
		$this->view->assign('preferred_tariff', $preferred);
		
		// PAYMENT
		$plugin_model = new smPluginModel();
		$instances = $plugin_model->listPlugins(smPluginModel::TYPE_PAYMENT, array('all' => true, ));
		if(count($instances))
		{
			foreach($instances as $key => $instance)
			{
				if($instance['status'] == 0) {unset($instances[$key]);}
			}
		}
		// Особый метод оплаты - по счету для юридического лица
		$instances['bill'] = array(
			'id' => -1,
			'name' => _w("payment by invoice"),
			'description' => _w("Payment by invoice for legal entities (Russian Federation)"),
			'logo' => wa()->getAppStaticUrl('sm').'img/payment-bill.png',
		);
		// Особый метод оплаты - внутрисистемный счет
		$instances['balance'] = array(
			'id' => 0,
			'name' => _w("System balance"),
			'description' => _w("Payment from an system balance. Available balance: ").wa_currency($user_data['balance'], '', '%2i').' '._w("rub."),
			'logo' => wa()->getAppStaticUrl('sm').'img/payment-balance.png',
		);
		$this->view->assign('is_bill_filled', smBillHelper::isBillFilled($user_id));
		$this->view->assign('instances', $instances);
		$this->view->assign('user_balance_amount', $user_data['balance']);
		
		$preferred = waRequest::get('method', '', 'string');
		if(!isset($instances[$preferred]))
		{
			foreach($instances as $key => $instance) {$preferred = $instance['id']; break;}
		}
		$this->view->assign('preferred_method', $preferred);

		$settings_model = new smSettingsModel();
        $this->view->assign('sm_settings', $settings_model->getSettings());
	}
}
