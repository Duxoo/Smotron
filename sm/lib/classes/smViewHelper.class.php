<?php

class smViewHelper
{
    public function preview($channel_code = '1hd')
    {
        $settings_model = new smSettingsModel();
        $settings = $settings_model->getSettings();
        $api = new smFlussonicApi();

        return $api->test($channel_code,$settings['flussonic_token']);
    }

    public function getSettings()
    {
        $settings_model = new smSettingsModel();
        return $settings_model->getSettings();
    }

    public function getBaseTariffs()
    {
        $tariff_channels_model = new smTariffChannelsModel();
        $tariff_model = new smTariffModel();
        $base_tariffs = $tariff_model->getPublicTariffs();
        foreach ($base_tariffs as $key => $tariff)
        {
            $base_tariffs[$key]['channels'] = $tariff_channels_model->getChannelsByTariff($tariff['id']);
        }
        return $base_tariffs;
    }

    public function getChannels()
    {
        $channel_model = new smChannelModel();
        $channels = $channel_model->getEnabledChannel();
        return $channels;
    }

    public function isSubuser()
    {
        $auth = new smAuth();
        $sub_auth = $auth->isSubAuth();
        if($sub_auth['result'] == 0){return false;}
        else{return true;}
    }

    public function subuserGet($field = null)
    {
        $auth = new smAuth();
        if(!isset($field)){ return $auth->getSubuserData();}
        else{return $auth->getSubuserData($field);}
    }
	
	public function skl($amount, $one, $two, $five)
	{
		if($amount < 10)
		{
			if($amount == 0) {return $five;}
			if($amount == 1) {return $one;}
			if($amount > 1 && $amount < 5) {return $two;}
			if($amount > 4) {return $five;}
		}
		if($amount > 9 && $amount < 20) {return $five;}
		if($amount % 10 == 0) {return $five;}
		if($amount % 10 == 1) {return $one;}
		if($amount % 10 > 1 && $amount % 10 < 5) {return $two;}
		if($amount % 10 > 4) {return $five;}
	}

	public function sklStat($amount, $one, $two, $five)
	{
		if($amount < 10)
		{
			if($amount == 0) {return $five;}
			if($amount == 1) {return $one;}
			if($amount > 1 && $amount < 5) {return $two;}
			if($amount > 4) {return $five;}
		}
		if($amount > 9 && $amount < 20) {return $five;}
		if($amount % 10 == 0) {return $five;}
		if($amount % 10 == 1) {return $one;}
		if($amount % 10 > 1 && $amount % 10 < 5) {return $two;}
		if($amount % 10 > 4) {return $five;}
	}
}