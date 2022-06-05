<?php

class smFrontendMyTariffAction extends waViewAction
{
    public function execute()
    {
        if(!wa()->getUser()->isAuth())
		{
			$this->setThemeTemplate('blank.html');
			wa()->getResponse()->redirect(wa()->getRouteUrl('sm/frontend').'?ar=1', 302);
			return;
		}
		
		$this->setThemeTemplate('my.tariff.html');
		$this->setLayout(new smFrontendLayout());
		$this->getResponse()->setTitle(_w("Subscription management — Smotron"));
		
		$user = new smUser(wa()->getUser()->getId());
		if(!$user->isUser())
		{
			$this->getResponse()->setTitle(_w("Error — Smotron"));
			$this->setThemeTemplate('error.html');
			$this->view->assign('error_title', _w("Subscription management error"));
			$this->view->assign('error', _w("Can't initialize the user"));
			$this->view->assign('error_no_disclaimer', 1);
			return;
		}
		
		$user_data = $user->getData();
		$this->view->assign('user_data', $user_data);
		$tariffs = smTariff::getUserTariffs($user_data['id']);
        $tariff_channels_model = new smTariffChannelsModel();
        foreach ($tariffs as $key => $tariff)
        {
            $tariffs[$key]['channels'] = $tariff_channels_model->getChannelsByTariff($tariff['id']);
        }
		$tariff_id = $user_data['tariff_id'];
		$tariff = null;
		if(isset($tariffs[$tariff_id])) {$tariff = $tariffs[$tariff_id];}

        $settings_model = new smSettingsModel();
		$settings = array(
		    'ctariff_channel_price' => $settings_model->getParam('ctariff_channel_price'),
            'ctariff_stream_price' => $settings_model->getParam('ctariff_stream_price'),
        );

		if($user_data['subscribtion_valid'] && $tariff !== null)
		{
			$this->view->assign('user_current_tariff', ifempty($tariffs[$user_data['tariff_id']], array()));
			$this->view->assign('subscribtion_refund', 
				smTariff::calculateRefundAmount(
					$user_data['subscribtion_cost_actual'],
					$user_data['subscribtion_activated'],
					$user_data['subscribtion_valid_till']
				)
			);
		}
        $this->view->assign('settings', $settings);
		$this->view->assign('tariff', $tariff);
		$this->view->assign('tariffs', $tariffs);
    }
}