<?php
class smTariff
{
    protected $id = null;
    protected $data = null;
    protected $model = null;
    protected $tariff_channels_model = null;

    public function __construct($id = null, $custom = false)
    {
        $this->model = new smTariffModel();
        $this->tariff_channels_model = new smTariffChannelsModel();
        $default_data = array(
            'name' => '',
            'base_range' => '',
            'custom_range' => '',
            'channels' => '',
            'price' => '',
            'description' => '',
            'sort' => '',
            'disabled' => '',
        );
        if($id)
        {
            $this->data = $this->model->getById($id);
            if(!$custom){$this->data['channels'] = $this->tariff_channels_model->getChannelsByTariff($id);}
            else{$this->data['channels'] = $this->tariff_channels_model->getChannelsByTariff($id,1);}
            if($this->data !== null)
            {
                if($this->data['hidden'] == 1) {$this->data = $default_data;}
                else {$this->id = $id; unset($this->data['id']);}
            }
            else {$this->data = $default_data;}
        }
        else {$this->data = $default_data;}
    }

    public function getId()
    {
        return $this->id;
    }

    public function get($field = null, $default = null)
    {
        if($field === null) {return $this->data;}
        return ifempty($this->data[$field], $default);
    }

    public function getData()
    {
        return $this->data;
    }

    public function set($field, $value)
    {
        $this->data[$field] = $value;
    }

    public function setAll($data)
    {
        $this->data = $data;
    }

    public function save()
    {
        if(isset($this->data['id'])) {unset($this->data['id']);}

        if($this->id) {$this->model->updateById($this->id, $this->data);}
        else {$this->id = $this->model->insert($this->data);}

        $this->tariff_channels_model->updateTariffChannels($this->id,$this->data);
    }

	public function getByField($field = null, $value = null)
    {
        if($field === null){return null;}
        else{return $this->model->getByField($field, $value, true);}
    }

    public function getChannels()
    {
        $channels_id = $this->data['channels'];
        $model = new smChannelModel();
        return $model->getChannelsById($channels_id);
    }

	static public function getUserTariffs($user_id)
	{
		$tariff_model = new smTariffModel();
		$tariffs = $tariff_model->getByUser($user_id);
		foreach($tariffs as $key => $tariff)
		{
			if($tariff['is_custom']) {$tariffs[$key]['price'] = self::calculateTariffPrice($tariff);}
		}
		return $tariffs;
	}

	static public function calculateTariffPrice($tariff)
	{
		if(!$tariff['is_custom']) {return $tariff['price'];}
        if($tariff['is_custom'] && isset($tariff['price'])) {return $tariff['price'];}

        $settings_model = new smSettingsModel();
        $settings = array(
            'channel_price' => $settings_model->getParam('ctariff_channel_price'),
            'stream_price' => $settings_model->getParam('ctariff_stream_price'),
        );

        $b_price = ($settings['channel_price'] * $tariff['channel_custom_count']) + ($settings['stream_price'] * $tariff['device_count']); //price without channels
        waLog::dump($tariff);
        $model = new smChannelModel();
        $channels = $model->getChannelsById($tariff['channels']);
        waLog::dump($tariff['channels']);
        $ch_price = 0;
        if(isset($tariff['channels']))
        {
            foreach ($channels as $id => $channel)
            {
                $ch_price += $channel['price'];
            }
        }
        $price = $ch_price + $b_price;

        $settings_model = new smSettingsModel();
        if($price <= $settings_model->getParam('ctariff_min_price')){ $price = $settings_model->getParam('ctariff_min_price'); }

        // TODO ИВАН: ВЫЧИСЛЕНИЕ СТОИМОСТИ ТАРИФА
		return $price;
	}

	static public function calculateRefundAmount($cost, $valid_from, $valid_till)
	{
		$refund_tariff_multiplier = 2;

		$days_total = max(ceil((strtotime($valid_till) - strtotime($valid_from))/86400),1);
		$days_used = max(ceil((time() - strtotime($valid_from))/86400), 1);
		$usage_percent = $days_used*100/$days_total;
        $cost_total = $days_total/30 * $cost;
		$amount = $cost_total - $cost_total*$usage_percent/100*$refund_tariff_multiplier;
		if($amount < 0) {$amount = 0;}

		return array(
			'cost_total' => $cost,
			'days_total' => $days_total,
			'days_used' => $days_used,
			'usage_percent' => $usage_percent,
			'amount' => $amount,
			'left_in_system' => $cost*$usage_percent/100*$refund_tariff_multiplier,
		);
	}

    static public function calculateChangeAmount($cost, $valid_from, $valid_till)
    {
        $refund_tariff_multiplier = 1;

        $days_total = max(ceil((strtotime($valid_till) - strtotime($valid_from))/86400),1);
        $days_used = max(ceil((time() - strtotime($valid_from))/86400), 1);
        $usage_percent = $days_used*100/$days_total;
        $amount = $cost - $cost*$usage_percent/100*$refund_tariff_multiplier;
        if($amount < 0) {$amount = 0;}

        return array(
            'cost_total' => $cost,
            'days_total' => $days_total,
            'days_used' => $days_used,
            'usage_percent' => $usage_percent,
            'amount' => $amount,
            'left_in_system' => $cost*$usage_percent/100*$refund_tariff_multiplier,
        );
    }
}