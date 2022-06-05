<?php
class smOrderHelper
{
	static public function getOrderStatuses()
	{
		return array(
			'new' => array(
				'type' => 'warning',
				'is_paid' => 0,
				'to_balance' => 0,
				'message' => '',
			),
			'processed' => array(
				'type' => 'success',
				'is_paid' => 1,
				'to_balance' => 0,
				'message' => '',
			),
			'no_user' => array(
				'type' => 'error',
				'is_paid' => 1,
				'to_balance' => 0,
				'message' => '',
			),
			'refund_no_tariff' => array(
				'type' => 'info',
				'is_paid' => 1,
				'to_balance' => 1,
				'message' => 'Пока заказ был в оплате, произошли изменения в тарифной сетке, заказанного тарифа больше не существует',
			),
			'override_subscribtion' => array(
				'type' => 'success',//'info',
				'is_paid' => 1,
				'to_balance' => 0,
				'message' => 'Ваш тариф продлен',//'На момент оплаты заказа у Вас уже была действующая подписка, и новую активировать невозможно',
			),
			'refund_no_upgrade' => array(
				'type' => 'info',
				'is_paid' => 1,
				'to_balance' => 1,
				'message' => 'Пока заказ был в оплате, произошли изменения в тарифной сетке, заказанный апгрейд более недоступен',
			),
			'override_upgrade' => array(
				'type' => 'info',
				'is_paid' => 1,
				'to_balance' => 1,
				'message' => 'На момент оплаты заказа у Ваша подписка уже была не хуже заказанного апгрейда',
			),
			'upgrade_inapplicable' => array(
				'type' => 'info',
				'is_paid' => 1,
				'to_balance' => 1,
				'message' => 'На момент оплаты апгрейда у Вас не было действующей подписки',
			),
			'outdated_upgrade' => array(
				'type' => 'info',
				'is_paid' => 1,
				'to_balance' => 1,
				'message' => 'Подписка, для которой покупался апгрейд, уже истекла',
			),
		);
	}
	
	static public function createOrder($user_id, $type, $tariff_id, $payment_id, $month_count, $extend = 0, $change = 0)
	{
	    if(($extend != 0) && ($change !=0)) { return array('result' => 0, 'message' => 'Ошибка оформления, Продление и изменение подписки нельзя делать одновременно!'); }

		$user = new smUser($user_id);
		if(!$user->isUser())
		{
			return array('result' => 0, 'message' => 'Чтобы совершить покупку, войдите в систему.');
		}
		$user_data = $user->getData();
		
		if($type != 'subscribe')
		{
			return array('result' => 0, 'message' => 'Ошибка оформления заказа. Вернитесь на страницу покупки подписки и оформите новый заказ.');
		}

		$order_model = new smOrderModel();
		$is_first = 0;
		$amount = 0;

        $settings_model = new smSettingsModel();
        $discount = $settings_model->getDiscountByMonthCount($month_count);

		// Проверка тарифа
		if($type == 'subscribe')
		{
			// Если подписка уже есть, новую заказать не получится
            if($user_data['subscribtion_valid'] && $extend != 1 && $change != 1) {return array('result' => 0, 'message' => 'У Вас уже действует активная подписка. Новую можно оформить, когда истечет старая.');}
			$tariffs = smTariff::getUserTariffs($user_id);
			$tariff_data = ifempty($tariffs[$tariff_id], null);
			if(!$tariff_data) {return array('result' => 0, 'message' => 'Выбранный тариф недоступен. Вернитесь на страницу покупки подписки и оформите новый заказ.');}
			$tariff_id = $tariff_data['id'];
			if(isset($month_count)) { $amount = $tariff_data['price'] * $month_count; $amount = $amount - ($amount * ($discount / 100)); }
			else{ $amount = $tariff_data['price']; }
		}
		
		// Проверка параметров оплаты
		$plugin_model = new smPluginModel();
		$plugins = $plugin_model->listPlugins(smPluginModel::TYPE_PAYMENT, array('all' => true, ));

		// Добавляем особый метод оплаты через внутрисистемный счет
		$plugins[-1] = array('plugin' => 'sm_bill');
		// Добавляем особый метод оплаты через внутрисистемный счет
		$plugins[0] = array('plugin' => 'sm_balance');
		
		if(!isset($plugins[$payment_id]))
		{
			return array('result' => 0, 'message' => 'Выбранный метод оплаты недоступен. Вернитесь на страницу покупки подписки и оформите новый заказ.');
		}
		
		// Проверка стоимости
		if($amount <= 0) {return array('result' => 0, 'message' => 'Ошибка определения суммы заказа. Вернитесь на страницу покупки подписки и оформите новый заказ.');}

		// Если выбран способ оплаты через внутрисистемный счет, то проверяем, хватает ли на нем денег
		if($payment_id == 0)
		{
			if($user_data['balance'] < $amount) {return array('result' => 0, 'message' => 'На внутрисистемном счете недостаточно средств для оплаты покупки.');}
		}

		if($change == 1){
            $user_data = $user->getData();

            $change_data = smSubscribtionHelper::getUpgradeOptions(
                $user_data['tariff_id'],
                $user_data['subscribtion_cost_actual'],
                $user_data['subscribtion_activated'],
                $user_data['subscribtion_valid_till']
            );
            waLog::dump($change_data);
            waLog::dump($user_data);

            $amount = $change_data[$tariff_id]['cost'];
        }

		$order_id = $order_model->create($user_id, $type, $month_count, $tariff_id, $tariff_data['name'], $payment_id, $plugins[$payment_id]['plugin'], $amount, $is_first);

        if($extend == 1){ $order_model->setStatus($order_id,'extension_subscribtion'); }  //Если продление меняем статус
        if($change == 1){ $order_model->setStatus($order_id,'override_subscribtion'); }   //Если изменение меняем статус

		$instant_payment = 0;
		
		// Если выбран способ оплаты через внутрисистемный счет, то сразу и оплачиваем
		if($payment_id == 0)
		{
		    if($extend == 1){$comment = 'Продление ';}
		    else{$comment = 'Покупка ';}
			if($type == 'subscribe') {$comment .= 'подписки на тариф ';}
			$comment .= $tariff_data['name'];
			if($change == 1){
			    $old_tariff = $tariffs[$user_data['tariff_id']] ;
			    $comment = 'Замена тарифа c '.$old_tariff['name'].' на '.$tariff_data['name'];
			}
			$user->transaction('purchase', -$amount, $order_id, $comment, $user->getId(), false);
			self::payment($order_id, $amount, $month_count);
			$instant_payment = 1;
		}
		
		return array('result' => 1, 'message' => 'Заказ оформлен', 'order_id' => $order_id, 'instant_payment' => $instant_payment);
	}
	
	static public function getOrderDescription($order_data, $count)
	{
		$description = 'Покупка ';
		if($order_data['type'] == 'subscribe') {
		    switch($count) {
                case 1:
                    $description .= 'подписки '.$order_data['tariff_name'].' на 30 дней';
                    break;
                case 3:
                    $description .= 'подписки '.$order_data['tariff_name'].' на 90 дней';
                    break;
                case 6:
                    $description .= 'подписки '.$order_data['tariff_name'].' на 180 дней';
                    break;
            }
		}
		return $description;
	}

	static public function payment($order_id, $captured_amount, $count = 1)
	{
		$order_model = new smOrderModel();
		$order_data = $order_model->getById($order_id);
		if(!$order_data) {return;}
		
		$user_id = $order_data['contact_id'];
		$user = new smUser($user_id);
		if(!$user->isUser())
		{
			$order_model->setStatus($order_id, 'no_user');
			$order_model->setPaid($order_id);
			return;
		}
		$user_data = $user->getData();
		
		$metrics = new smMetricsModel();
		$metrics->increment('income');//, $order_data['total']
		
		// Проверка на повторную оплату заказа
		if($order_data['paid_date'])
		{
			$user->transaction('refund_payment_repeat', $order_data['total'], $order_id, 'Возврат суммы заказа - повторная оплата', 0, false);
			return;
		}
		
		// Заказ на оформление подписки
		if($order_data['type'] == 'subscribe')
		{
			$tariff_model = new smTariffModel();
			$tariff_data = $tariff_model->getById($order_data['tariff_id']);
			
			// Если тарифа, на который был оформлен заказ, уже нет, вернем деньги на счет
			if(!$tariff_data)
			{
				$user->transaction('refund_no_tariff', $order_data['total'], $order_id, 'Невозможно активировать подписку - не найден тариф. Возврат оплаты за активацию', 0, false);
				$order_model->setStatus($order_id, 'refund_no_tariff');
				$order_model->setPaid($order_id);
				return;
			}
			
			// Если действующая подписка уже есть, то зачисляем денежки на внутрисистемный счет
			if($user_data['subscribtion_valid'])
			{
				//$user->transaction('refund_order', $order_data['total'], $order_id, 'Невозможно активировать подписку - она уже есть. Возврат оплаты за активацию', 0, false);
                if($order_data['status'] == 'extension_subscribtion')
                {
                    $user->extendSubscribtion($order_data['tariff_id'], 30*$count);
                    $order_model->setStatus($order_id, 'processed');
                    $order_model->setPaid($order_id);
                }
                elseif ($order_data['status'] == 'override_subscribtion')
                {
                    $user->upgradeSubscribtion($order_data['tariff_id'], 30*$count);
                    $order_model->setStatus($order_id, 'processed');
                    $order_model->setPaid($order_id);
                }
                else
                {
                    $user->transaction('refund_order', $order_data['total'], $order_id, 'Невозможно активировать подписку - она уже есть. Возврат оплаты за активацию', 0, false);
                }
				return;
			}
			
			// Если действущей подписки нет, активируем
			$user->activateSubscribtion($order_data['tariff_id'], 30*$count);
			$order_model->setStatus($order_id, 'processed');
			$order_model->setPaid($order_id);
			
			// Если это первый успешный заказ, так его и маркируем
			if($order_model->countPaidOrders($user_id) == 1) {$order_model->updateById($order_id, array('is_first' => 1));}
		
			// Обновим системные метрики
			$metrics->increment('subscribed', $order_data['total']);
		}
		
		// Если заказ оформлен успешно, возвратов нет, необходимо задействовать реферальную программу
		if($order_data['payment_id'] != 0)
		{
			//smReferalHelper::addPayout($user_id, $order_data['total'], $order_id, 30);
            smReferralHelper::addPayout($user_id, $order_data['total'], $order_id);
		}
	}
}
