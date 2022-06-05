<?php
class smUser
{
	protected $id = null;
	protected $model = null;
	protected $data = null;
	protected $contact_data = null;
	protected $event_model = null;
	
	public function __construct($id = null)
    {
		if($id === null)
		{
			if(wa()->getUser()->isAuth()) {$id = wa()->getUser()->getId();}
			else {$id = 0;}
		}
		
		$this->model = new smUserModel();
		$this->data = $this->model->getById($id);
		if($this->data['deleted']) {return;}
		
		$this->event_model = new smUserEventModel();
		
		$contact_model = new waContactModel();
		$this->contact_data = $contact_model->getById($id);
		
		// Данные контакта найдены
		if($this->data)
		{
			if($this->contact_data) {$this->id = $id;}
			else {$this->model->updateById($id, array('deleted' => 1)); return;}
		}
		// Данные контакта не найдены
		else
		{
			if($this->contact_data) {$this->data = $this->create($id); $this->id = $id;}
			else {return;}
		}
		
		// Заполнить данные о подписке
		$this->data['subscribtion_valid'] = 0;
		if($this->data['tariff_id'])
		{
			if($this->data['subscribtion_valid_till'] > date('Y-m-d H:i:s')) {$this->data['subscribtion_valid'] = 1;}
		}
	}
	public function set($field = null , $value = null) { if($field !== null){ $this->data[$field] = $value; } }
	public function getId() {return $this->id;}
	public function getData($param = null)
	{
		if($param !== null) {return ifempty($this->data[$param], null);}
		return $this->data;
	}
	public function getContactData($param = null)
	{
		if($param !== null) {return ifempty($this->contact_data[$param], null);}
		return $this->contact_data;
	}
	
	public function save()
	{
		if(!$this->id) {return;}
		$data = $this->data;
		unset($data['id']);
		$this->model->updateById($this->id, $data);
	}
	
	protected function create($id)
	{
		//$metrics = new smMetricsModel();
		//$metrics->increment('new_users');
		$data = array(
			'id' => $id,
			'balance' => 0,
		);
		$this->model->insert($data);
		
		return $this->model->getById($id);
	}
	
	public function isUser()
	{
		if($this->id) {return 1;}
		return 0;
	}

    public function getToken()
    {
        $rel_model = new smFluserSmuserModel();
        $user_data = $rel_model->getById($this->data['id']);
        return $user_data['fl_token'];
    }

	public function transaction($type, $amount, $entity_id, $comment, $emit_id = 0, $is_safe = true)
	{
		if(!$this->id) {return array('result' => 0, 'message' => 'Пользователь не инициализирован');}
		if(!mb_strlen(trim($comment))) {return array('result' => 0, 'message' => 'Комментарий не может быть пустым');}
		$balance_current = $this->data['balance'];
		if($balance_current + $amount < 0 && $amount < 0 && $is_safe) {return array('result' => 0, 'message' => 'Не хватает средств для совершения транзакции');}
		$balance_before = $balance_current;
		$balance_after = $balance_current + $amount;
		$this->data['balance'] = $balance_after;
		$this->model->updateById($this->id, array('balance' => $this->data['balance']));
		
		$history_model = new smUserBalanceHistoryModel();
		$history_model->insert(array(
			'user_id' => $this->id,
			'type' => $type,
			'amount' => $amount,
			'amount_before' => $balance_before,
			'amount_after' => $balance_after,
			'comment' => $comment,
			'entity_id' => $entity_id,
			'emit_id' => $emit_id,
			'transaction_datetime' => date('Y-m-d H:i:s'),
			'transaction_date' => date('Y-m-d'),
		));
		return array('result' => 1, 'message' => 'Транзакция завершена', 'after' => $balance_after);
	}

	public function activateSubscribtion($tariff_id, $days = 30, $admin_id = null, $promocode = null)
	{
		if(!$this->id) {return array('result' => 0, 'message' => 'Пользователь не найден');}
		if($days <= 0) {return array('result' => 0, 'message' => 'Количество дней должно быть положительным');}
		if($this->data['subscribtion_valid']) {return array('result' => 0, 'message' => 'Уже есть дейсвующая подписка');}

        $settings_model = new smSettingsModel();
        $discount = $settings_model->getDiscountByMonthCount($days/30);

		$tariff_model = new smTariffModel();
		$tariff_data = $tariff_model->getById($tariff_id);
		if(!$tariff_data) {return array('result' => 0, 'message' => 'Тариф не найден');}


		$cost = ($tariff_data['price']/30)*$days;
		$cost_actual = $cost - ($cost * ($discount / 100));

		if($admin_id) {$cost_actual = 0;}
		
		$this->data['tariff_id'] = $tariff_id;
		$this->data['subscribtion_cost'] = $cost;
		$this->data['subscribtion_cost_actual'] = $cost_actual;
		$this->data['subscribtion_activated'] = date('Y-m-d H:i:s');
		$this->data['subscribtion_valid_till'] = date('Y-m-d H:i:s', time() + ($days * 86400));
		$this->data['subscribtion_admin_id'] = $admin_id;

		$this->save();
		
		if($admin_id)
		{
			$message_datetime = date('d.m.Y H:i:s', strtotime($this->data['subscribtion_valid_till']));
			if($promocode !== null)
			{
				$type = 'promo';
				$text = 'Активирован промокод '.$promocode.': подписка '.$tariff_id.' в подарок до '.$message_datetime;
			}
			else
			{
				$type = 'gift';
				$text = 'Администратор: '.$tariff_id.' в подарок до '.$message_datetime;
			}
			$this->event_model->addEvent($this->id, $type, $text, $admin_id);
		}
		else {$this->event_model->addEvent($this->id, 'purchase', 'Покупка подписки '.$tariff_id.' в до '.$this->data['subscribtion_valid_till'].' за '.wa_currency($cost_actual, '', '%2i').' руб.', wa()->getUser()->getId());}
		
		$order_model = new smOrderModel();
		$order_model->outdateUpgrades($this->id);
        $token_class = new smToken();
        $token_class->updateUserToken($this->id, $tariff_data['device_count']);

		return array('result' => 1, 'message' => 'Подписка активирована');
	}

    // продление подписки
    public function extendSubscribtion($tariff_id, $days = 30, $admin_id = null, $promocode = null)
    {
        if(!$this->id) {return array('result' => 0, 'message' => 'Пользователь не найден');}
        if($days <= 0) {return array('result' => 0, 'message' => 'Количество дней должно быть положительным');}
        if(!$this->data['subscribtion_valid']) {return array('result' => 0, 'message' => 'Нет дейсвующей подписки');}

        $settings_model = new smSettingsModel();
        $discount = $settings_model->getDiscountByMonthCount($days/30);

        $tariff_model = new smTariffModel();
        $tariff_data = $tariff_model->getById($tariff_id);
        if(!$tariff_data) {return array('result' => 0, 'message' => 'Тариф не найден');}


        $cost = ($tariff_data['price']/30)*$days;
        $cost_actual = $cost - ($cost * ($discount / 100));

        if($admin_id) {$cost_actual = 0;}

        $this->data['tariff_id'] = $tariff_id;
        $this->data['subscribtion_cost'] = $cost;
        $this->data['subscribtion_cost_actual'] = $cost_actual;
        $this->data['subscribtion_activated'] = date('Y-m-d H:i:s');
        $this->data['subscribtion_valid_till'] = date('Y-m-d H:i:s', strtotime($this->data['subscribtion_valid_till'])+($days * 86400));
        $this->data['subscribtion_admin_id'] = $admin_id;

        $this->save();

        if($admin_id)
        {
            $message_datetime = date('d.m.Y H:i:s', strtotime($this->data['subscribtion_valid_till']));
            if($promocode !== null)
            {
                $type = 'promo';
                $text = 'Активирован промокод '.$promocode.': подписка '.$tariff_id.' в подарок до '.$message_datetime;
            }
            else
            {
                $type = 'gift';
                $text = 'Администратор: '.$tariff_id.' в подарок до '.$message_datetime;
            }
            $this->event_model->addEvent($this->id, $type, $text, $admin_id);
        }
        else {$this->event_model->addEvent($this->id, 'extend', 'Продление подписки '.$tariff_id.' в до '.$this->data['subscribtion_valid_till'].' за '.wa_currency($cost_actual, '', '%2i').' руб.', wa()->getUser()->getId());}

        $order_model = new smOrderModel();
        $order_model->outdateUpgrades($this->id);

        $token_class = new smToken();
        $token_class->updateUserToken($this->id, $tariff_data['device_count']);

        return array('result' => 1, 'message' => 'Подписка продлена');
    }

	public function upgradeSubscribtion($tariff_id, $days = 30, $admin_id = null, $promocode = null)
	{
		if(!$this->id) {return array('result' => 0, 'message' => 'Пользователь не найден');}
		if(!$this->data['subscribtion_valid']) {return array('result' => 0, 'message' => 'Нет действующей подписки');}
		
		$tariff_model = new smTariffModel();
		$tariff_data = $tariff_model->getById($tariff_id);
		if(!$tariff_data) {return array('result' => 0, 'message' => 'Новый тариф не найден');}
		
		$upgrades = smSubscribtionHelper::getUpgradeOptions(
			$this->data['tariff_id'],
			$this->data['subscribtion_cost'], 
			$this->data['subscribtion_activated'],
			$this->data['subscribtion_valid_till']
		);
		
		if(!isset($upgrades[$tariff_id])) {return array('result' => 0, 'message' => 'Нельзя прозвести апгрейд до указанного тарифа');}
		
		$previous_tariff_id = $this->data['tariff_id'];
		$this->data['tariff_id'] = $tariff_id;
		$this->data['subscribtion_cost'] += $upgrades[$tariff_id]['cost'];
		if(!$admin_id) {$this->data['subscribtion_cost_actual'] += $upgrades[$tariff_id]['cost'];}
		if($admin_id) {$this->data['subscribtion_admin_id'] = $admin_id;}
		
		$this->save();
		
		if($admin_id) {$this->event_model->addEvent($this->id, 'gift', 'Улучшение тарифа c '.$previous_tariff_id.' до '.$tariff_id.' в подарок', $admin_id);}
		else {$this->event_model->addEvent($this->id, 'purchase', 'Улучшения тарифа с '.$previous_tariff_id.' до '.$tariff_id.' за '.wa_currency($upgrades[$tariff_id]['cost'], '', '%2i').' руб.', wa()->getUser()->getId());}

		$token_class = new smToken();
        $token_class->updateUserToken($this->id, $tariff_data['device_count']);

		return array('result' => 1, 'message' => 'Апгрейд произведен');
	}
	
	public function refundSubscribtion()
	{
		if(!$this->id) {return array('result' => 0, 'message' => 'Пользователь не найден');}
		if(!$this->data['subscribtion_valid']) {return array('result' => 0, 'message' => 'Нет дейсвующей подписки - нечего возвращать');}
		
		$refund_data = smTariff::calculateRefundAmount(
			$this->data['subscribtion_cost_actual'],
			$this->data['subscribtion_activated'],
			$this->data['subscribtion_valid_till']
		);

		if($refund_data['amount'] > 0)
		{
			$comment = 'Возврат средств за подписку '.$this->data['tariff_id'].' - использовано дней '.$refund_data['days_used'].' из '.$refund_data['days_total'];
			$transaction = $this->transaction('refund', $refund_data['amount'], 0, $comment, wa()->getUser()->getId(), true);
			if(!$transaction['result']) {return array('result' => 0, 'message' => 'Ошибка зачисления средств на счет');}
		}
		
		$old_cost_actual = $this->data['subscribtion_cost_actual'];
		$this->data['subscribtion_valid_till'] = date('Y-m-d H:i:s');
		$this->data['subscribtion_cost_actual'] = $this->data['subscribtion_cost_actual'] - $refund_data['amount'];
		$this->save();

		$usage_skl = $refund_data['days_used'].' '.(new smViewHelper())->sklStat($refund_data['days_used'], 'день', 'дня', 'дней').' из '.$refund_data['days_total'].' использовано';
		$this->event_model->addEvent($this->id, 'refund', 'Возврат подписки '.$this->data['tariff_id'].', '.$usage_skl.', '.wa_currency($refund_data['amount'], '', '%2i').' из '.wa_currency($old_cost_actual, '', '%2i').' руб. вернулось на счет', wa()->getUser()->getId());
		
		// Обработка реферальных начислений в связи с возвратом
		//smReferalHelper::processRefund($this->id, $refund_data['left_in_system']);

        $metrics = new smMetricsModel();
		$metrics->increment('unsubscribed');
		$metrics->increment('refund', $refund_data['amount']);
		$metrics->increment('expence', $refund_data['amount']);
        $token_class = new smToken();
        $token_class->updateUserToken($this->id);

		return array('result' => 1, 'message' => 'Возврат произведен');
	}

	public function acceptOfferta()
	{
		if(!$this->id) {return;}
		if($this->data['offerta_accept']) {return;}
		$this->data['offerta_accept'] = date('Y-m-d H:i:s');
		$this->save();
	}

	public function updateBalance($amount)
    {
        $data = array(
            'balance' => $amount,
        );
        return $this->model->updateById($this->id, $data);
    }

    public function getUserCustomChannels()
    {
        $ch_model = new smChannelModel();
        return $ch_model->getChannelsByUser($this->id);
    }
}
