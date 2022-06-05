<?php

class smPayment extends waAppPayment
{
    private static $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {self::$instance = new self();}
        return self::$instance;
    }

    protected function init()
    {
        $this->app_id = 'sm';
        parent::init();
    }

	private function model()
    {
        static $model;
        if (!$model) {$model = new smPluginSettingsModel();}
        return $model;
    }

    public static function getPlugin($plugin, $plugin_id = null)
    {
        if (!$plugin && $plugin_id) 
		{
            $model = new smPluginModel();
            $info = $model->getById($plugin_id);

            if (!$info) {throw new waException("Payment plugin {$plugin_id} not found", 404);}
            if ($info['type'] != smPluginModel::TYPE_PAYMENT) {throw new waException("Payment plugin {$plugin_id} has invalid type", 404);}
            $plugin = $info['plugin'];
        }
        return waPayment::factory($plugin, $plugin_id, self::getInstance());
    }

    public static function getPluginInfo($id)
    {
        if ($plugin_id = max(0, intval($id)))
		{
            $model = new smPluginModel();
            $info = $model->getById($plugin_id);
            if (!$info) {throw new waException("Payment plugin {$plugin_id} not found", 404);}
        }
		else
		{
            $info = array(
                'plugin' => $id,
                'status' => 1,
            );
        }

        $default_info = waPayment::info($info['plugin']);
        return is_array($default_info) ? array_merge($default_info, $info) : $default_info;
    }

    public static function savePlugin($plugin)
    {
        $default = array(
            'status' => 0,
        );
        $plugin = array_merge($default, $plugin);
		
        $model = new smPluginModel();
        if(!empty($plugin['id']) && ($id = max(0, intval($plugin['id']))) && ($row = $model->getByField(array('id' => $id, 'type' => smPluginModel::TYPE_PAYMENT))))
		{
            $plugin['plugin'] = $row['plugin'];
            $model->updateById($plugin['id'], $plugin);
        }
		elseif(!empty($plugin['plugin']))
		{
            $plugin['type'] = smPluginModel::TYPE_PAYMENT;
            $plugin['id'] = $model->insert($plugin);
        }
        if (!empty($plugin['id']) && isset($plugin['settings'])) {
            waPayment::factory($plugin['plugin'], $plugin['id'], self::getInstance())->saveSettings($plugin['settings']);
        }
        if (!empty($plugin['id']))
		{
            ifset($plugin['shipping'], array());
            $plugins = $model->listPlugins(smPluginModel::TYPE_SHIPPING, array('all' => true,));
            $app_settings = new waAppSettingsModel();
            $settings = json_decode($app_settings->get('sm', 'shipping_payment_disabled', '{}'), true);
            if (empty($settings) || !is_array($settings)) {$settings = array();}
            if (!isset($settings[$plugin['id']])) {$settings[$plugin['id']] = array();}
            $s =& $settings[$plugin['id']];
            foreach ($plugins as $item)
			{
                if (empty($plugin['shipping'][$item['id']])) {$s[] = $item['id'];}
				else
				{
                    $key = array_search($item['id'], $s);
                    if ($key !== false) {unset($s[$key]);}
                }
            }

            $s = array_unique($s);

            if (empty($s)) {unset($settings[$plugin['id']]);}
            $app_settings->set('sm', 'shipping_payment_disabled', json_encode($settings));
        }
        return $plugin;
    }

    /**
     *
     * formalize order data
     * @param string|array $order order ID or order data
     * @param waPayment|string|string[] $payment_plugin
     * @return waOrder
     * @throws waException
     *
     * @todo: $payment_plugin param
     */
    public static function getOrderData($order, $payment_plugin = null)
    {
		$payment_name = '';
		$plugin_model = new smPluginModel();
		$plugin_info = $plugin_model->getById($order_data['payment_id']);
		if($plugin_info) {if($plugin_info['type'] == smPluginModel::TYPE_PAYMENT) {$payment_name = $plugin_info['name'];}}
		
		$order_data = array(
			'id_str'           => $order['id'],
			'id'               => $order['id'],
			'contact_id'       => $order['contact_id'],
			'datetime'         => ifempty($order['create_datetime']),
			'description'      => $order['comment'],
			'update_datetime'  => null,
			'paid_datetime'    => null,
			'total'            => $order['amount'],
			'currency'         => 'RUB',
			'discount'         => 0,
			'tax'              => 0,
			'payment_name'     => $payment_name,
			'billing_address'  => '',
			'shipping'         => null,
			'shipping_name'    => '',
			'shipping_address' => '',
			'items'            => array(),
			'comment'          => '',
			'params'           => array(),
        );
        return waOrder::factory($order_data);
    }

    public function getDataPath($order_id, $path = null)
    {
        $str = str_pad($order_id, 4, '0', STR_PAD_LEFT);
        $path = 'orders/'.substr($str, -2).'/'.substr($str, -4, 2).'/'.$order_id.'/payment/'.$path;
        return wa('sm')->getDataPath($path, false, 'sm');
    }

    public function getSettings($payment_id, $merchant_key) {$this->merchant_id = max(0, intval($merchant_key));  return $this->model()->get($merchant_key);}
    public function setSettings($plugin_id, $key, $name, $value) {$this->model()->set($key, $name, $value);}
    public function cancel() {}
    public function refund() {}
    public function auth() {}
    public function capture() {}
    public function payment() {}
    public function void() {}
    public function paymentForm() {}

    public function getBackUrl($type = self::URL_SUCCESS, $transaction_data = array())
    {
		switch ($type) {
            case self::URL_PRINTFORM:
                ifempty($transaction_data['printform'], 0);
                $params = array(
                    'order_id' => $transaction_data['order_id'],
                    'form_type' => 'payment',
                    'form_id'   => ifempty($transaction_data['printform'], 'payment'),
                );
                $url = wa()->getRouteUrl('sm/frontend/paymentPrintform', $params, true);
                break;
            case self::URL_SUCCESS:
                $url = wa()->getRouteUrl('sm/frontend/paymentResult', array(), true);
				if(!empty($transaction_data['order_id'])) {
                    $url .= '?order_id='.$transaction_data['order_id'];
                }
				break;
            case self::URL_FAIL:
            case self::URL_DECLINE:
				$url = wa()->getRouteUrl('sm/frontend/paymentResult', array(), true);
				if(!empty($transaction_data['order_id'])) {
                    $url .= '?order_id='.$transaction_data['order_id'];
                }
				break;
            default:
                $url = wa()->getRouteUrl('sm/frontend', true);
                break;
        }
        return $url;
    }

    /**
     * @param array $transaction_data
     * @return array
     */
    public function callbackPaymentHandler($transaction_data)
    {
        waLog::dump($transaction_data);

        $order_model = new smOrderModel();
        $order_data = $order_model->getById($transaction_data['order_id']);

		$order_id = $transaction_data['order_id'];
		$captured_amount = $transaction_data['amount'];
		//echo 'callback ready '.$order_id.' '.$captured_amount;
		
		smOrderHelper::payment($order_id, $captured_amount, $order_data['count']);
		//return array('contact_id' => $order_data['contact_id']);
    }

    /**
     * @param array $transaction_data
     * @return array
     */
    public function callbackCancelHandler($transaction_data)
    {
        return true;
    }

    /**
     * @param array $transaction_data
     * @return array
     */
    public function callbackDeclineHandler($transaction_data)
    {
        return true;
    }

    /**
     * @param array $transaction_data
     * @return array
     */
    public function callbackRefundHandler($transaction_data)
    {
       return true;
    }

    /**
     * @param array $transaction_data
     * @return array
     */
    public function callbackCaptureHandler($transaction_data)
    {
       return true;
    }

    /**
     * @param array $transaction_data
     * @return array
     */
    public function callbackChargebackHandler($transaction_data)
    {
        return true;
    }

    /**
     * @param array $transaction_data
     * @return array
     */
    public function callbackConfirmationHandler($transaction_data)
    {
        return true;
    }

    /**
     * @param array $transaction_data
     * @return array
     */
    public function callbackNotifyHandler($transaction_data)
    {
        return true;
    }
}
