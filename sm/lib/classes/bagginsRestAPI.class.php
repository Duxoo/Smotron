<?php
class bagginsRestAPI
{
	public function processCommand($parameters)
	{
		$command = ifempty($parameters['command'], '');
		switch($command)
		{
			case 'smsAuth':
				return $this->commandSmsAuth($parameters);
				break;
			//{"status":"ok","data":{"result":1,"message":"SUCCESS: HERE IS YOUR SESSION","session_code":"75782ac4-77df-11e8-85ca-525400d9acba"}}
			case 'sessionAcquire':
				return $this->commandSessionAcquire($parameters);
				break;
			case 'sessionAcquireAnonimous':
				return $this->commandSessionAnonimous($parameters);
				break;
			case 'isSessionValid':
				return $this->isSessionValid($parameters);
				break;
			case 'listShops':
				return $this->commandListShops($parameters);
				break;
			case 'setShop':
				return $this->commandSetShopId($parameters);
				break;
			case 'getMenu':
				return $this->commandGetMenu($parameters);
				break;
			case 'cartAdd':
				return $this->commandCartAdd($parameters);
				break;
			case 'cartDelete':
				return $this->commandCartDelete($parameters);
				break;
			case 'cartTotal':
				return $this->commandCartTotal($parameters);
				break;
			case 'cartItems':
				return $this->commandCartItems($parameters);
				break;
			case 'orderCreate':
				return $this->commandOrderCreate($parameters);
				break;
			// Команды работы с покупателями
			case 'setCustomerData':
				return $this->commandSetCustomerData($parameters);
				break;
			case 'getCustomerData':
				return $this->commandGetCustomerData($parameters);
				break;
			case 'setCustomerDefaultInvite':
				return $this->commandSetCustomerDefaultInvite($parameters);
				break;
			case 'setCustomerInvite':
				return $this->commandSetCustomerInvite($parameters);
				break;
			case 'getCustomerInvite':
				return $this->commandGetCustomerInvite($parameters);
				break;
			case 'getCustomerStructure':
				return $this->commandGetCustomerStructure($parameters);
				break;
			case 'getCustomerAccount':
				return $this->commandGetCustomerAccount($parameters);
				break;
			// Команды с проверкой ключа внешнего источника
			case 'trustedAuth':
				return $this->commandTrustedAuth($parameters);
				break;
			// Сервисные команды
			case 'sysTime':
				return $this->commandSysTime($parameters);
				break;
			/*MAX*/
			// Загрузка additions 
			case 'getProductAdditions':
				return $this->commandGetProductAdditions($parameters);
				break;	
			// Проверка интернета
			case 'checkInternet':
				return $this->commandCheckInternet();
				break;	
			// Предпочитаемая кофейня
			case 'getPrefferredShop':
				return $this->commandGetPrefferredShop($parameters);
				break;	
			// Объединение сессий
			case 'mergeSession':
				return $this->commandMergeSession($parameters);
				break;
			//Запрос на оплату банковской картой
			case 'paySberbankCard':
				return $this->commandPaySberbabkCard($parameters);
				break;
			//Заказы
			case 'getFullOrderInfo':
				return $this->getFullOrderInfo($parameters);
				break;		
			case 'getOrders':
				return $this->getOrdersBySession($parameters);
				break;
			default:
				return array('result' => 0, 'message' => 'ERROR: ILLEGAL COMMAND');
				break;
		}
		return array('result' => 0, 'message' => 'ERROR: ILLEGAL COMMAND');
	}
	
	/////////////////////////////////////////////////////////////////////////////////////////////
	// COMMANDS
	/////////////////////////////////////////////////////////////////////////////////////////////
	
	public function commandSmsAuth($parameters)
	{
		// Phone format 79501234567
		$phone = $this->validatePhone(ifempty($parameters['phone'], ''));
		if(!$phone) {return array('result' => 0, 'message' => 'ERROR: INVALID PHONE');}

		$contact_data_model = new waContactDataModel();
		$contact_data = $contact_data_model->query("SELECT * FROM ".$contact_data_model->getTableName()."
													WHERE field = 'phone' AND value = s:phone ORDER BY contact_id ASC LIMIT 1", array('phone' => $phone))->fetchAll();
		if(!count($contact_data)) {$contact_data = null;} else {$contact_data = $contact_data[0];}
		if(!$contact_data)
		{
			$contact = new waContact();
			$contact->set('phone', $phone);
			$contact->save();
			$contact_id = $contact->getId();
		}
		else
		{
			$contact_id = $contact_data['contact_id'];
		}
		$session_model = new bagginsRestSessionModel();
		$session_code = $session_model->getSessions($contact_id);
		if(!$session_code) 
		{
			$session_code = $session_model->createSession($contact_id, $phone);
		}
		$session_data = $session_model->getById($session_code);
		if(!$session_data) {return array('result' => 0, 'message' => 'ERROR: COULD NOT CREATE SESSION');}
		
		$text = 'Код авторизации: '.$session_data['auth_code'];
		$sms = new waSMS();
		$sms->send('+'.$phone, $text, null);
		return array('result' => 1, 'message' => 'SUCCESS: SMS SENT');
	}
	
	public function commandSessionAcquire($parameters)
	{
		$auth_code = ifempty($parameters['code'], '');
		$phone = ifempty($parameters['phone'], '');
		$session_model = new bagginsRestSessionModel();
		$session_data = $session_model->getByAuthCodeAndPhone($auth_code, $phone);
		if(!$session_data) {return array('result' => 0, 'message' => 'ERROR: INVALID CODE');}
		$session_model->activateSession($session_data['code']);
		return array('result' => 1, 'message' => 'SUCCESS: HERE IS YOUR SESSION', 'session_code' => $session_data['code']);
	}

	public function commandSessionAnonimous($parameters)
	{
		$session_model = new bagginsRestSessionModel();
		$session_code = $session_model->createAnonimousSession();
		return array('result' => 1, 'message' => 'SUCCESS: HERE IS YOUR SESSION', 'session_code' => $session_code);
	}
	
	public function commandListShops($parameters)
	{
		$rest_shop_model = new bagginsRestShopModel();
		return $rest_shop_model->getFullUnfetchedList();
	}

	public function commandSetShopId($parameters)
	{
		$session = $this->getSession($parameters);
		if(!$session) {return array('result' => 0, 'message' => 'ERROR: PLEASE AUTH FIRST');}
		
		$shop_id = ifempty($parameters['shop_id'], 0);
		$shop_model = new bagginsRestShopModel();
		if(!$shop_model->getById($shop_id)) {return array('result' => 0, 'message' => 'ERROR: INVALID SHOP ID');}
	
		$session_model = new bagginsRestSessionModel();
		$session_model->updateById($session['code'], array('shop_id' => $shop_id));
		return array('result' => 1, 'message' => 'SUCCESS: SHOP ID UPDATED');
	}
	
	public function commandGetMenu($parameters)
	{
		$shop_id = intval(ifempty($parameters['shop_id'], 0));
		$shop_model = new bagginsShopModel();
		$shop_data = $shop_model->getById($shop_id);
		if(!$shop_data) {return array('result' => 0, 'message' => 'ERROR: INVALID SHOP ID');}
		
		$rest_shop_model = new bagginsRestShopModel();
		if(!$rest_shop_model->getById($shop_id)) {return array('result' => 0, 'message' => 'ERROR: INVALID SHOP ID');}
	
		$result = array();
		
		$rest_category_model = new bagginsRestCategoryModel();
		$rest_category_products_model = new bagginsRestCategoryProductsModel();
		$list_category = $rest_category_model->getAllSorted();
		//return $list_category;
		foreach($list_category as $key => $a)
		{
			if(($rest_category_products_model->getCountProduct($shop_id, $key)) == 0)
			{
				unset($list_category[$key]);
			}
		}
			
		$result['categories'] = $list_category;
		$result['categories'] = $this->arrayUnKey($result['categories']);
		

		$product_images_model = new bagginsProductImagesModel();
		foreach ($result['categories'] as $r)
		{
			$list_product = $rest_category_products_model->getAPIProduct($shop_id, $r['id']);
			if($list_product)
			{
				foreach($list_product as $k => $product)
				{
					$images = $product_images_model->query("SELECT * FROM ".$product_images_model->getTableName()."
													WHERE product_id IN(i:products)", array('products' => $product['id']))->fetchAll('id');

					$product_images = array();
					//if(!count($images)) {$result['product_images'][$product['id']] = null;}
					if(!count($images)) {$result['product_images'][$product['id']] = array();}
					else
					{
						foreach($images as $key => $image)
						{
							$image_url = bagginsProduct::getImageUrl($image, '970x0', true);
							$image_id = $image['id'];
							$product_images[$image_id] = array(
								'id' => $image_id,
								'product_id' => $image['product_id'],
								'sort' => $image['sort'],
								'url' => $image_url,
							);
							$result['product_images'][$product['id']] = $this->arrayUnKey($product_images);
						}
					}
					
					$additions = $this->arrayUnKey($rest_category_products_model->getAPIAdditions($shop_id, $product['id']));
					if(count($additions))
					{
						$list_product[$k]['additions'] = 1;
					}
					else{
						$list_product[$k]['additions'] = 0;
					}
				}
				$result['products'][$r['id']] = $this->arrayUnKey($list_product);
			}
		}
		return $result;
	}

	public function commandCartAdd($parameters)
	{
		$session = $this->getSession($parameters);
		if(!$session) {return array('result' => 0, 'message' => 'ERROR: PLEASE AUTH FIRST');}
		
		$product_id = intval(ifempty($parameters['product_id'], 0));
		$quantity = intval(ifempty($parameters['quantity'], 1));
		if($quantity < 1) {$quantity = 1;}
		$rest_category_products_model = new bagginsRestCategoryProductsModel();
		if(!$rest_category_products_model->productAvaliable($product_id)) {return array('result' => 0, 'message' => 'ERROR: INVALID PRODUCT ID');}
		
		$additions = ifempty($parameters['additions'], array());
		if(!is_array($additions)) {$additions = array();}
		if(!count($additions)) {$additions = array();}
		else
		{
			foreach($additions as $key => $addition)
			{
				if(!is_array($addition)) {unset($additions[$key]); continue;}
				if(!isset($addition['id'])) {unset($additions[$key]); continue;}
				if(!isset($addition['quantity'])) {$additions[$key]['quantity'] = 1;}
				if(intval($key) != intval($addition['id'])) {unset($additions[$key]); continue;}
				$additions[$key]['id'] = intval($addition['id']);
				$additions[$key]['quantity'] = intval($addition['quantity']);
			}
		}
		
		$cart = new bagginsRestCart($session['code'], $session['shop_id'], $session['contact_id']);
		$result = $cart->add($product_id, $quantity, $additions);
		if(!$result['result']) {return $result;}
		return array('result' => 1, 'message' => 'SUCCESS: ITEM ADDED', 'total' => $cart->getTotal());
	}
	
	public function commandCartDelete($parameters)
	{
		$session = $this->getSession($parameters);
		if(!$session) {return array('result' => 0, 'message' => 'ERROR: PLEASE AUTH FIRST');}
		
		$item_id = intval(ifempty($parameters['item_id'], 0));
		$cart = new bagginsRestCart($session['code'], $session['shop_id'], $session['contact_id']);
		$cart->delete($item_id);
		
		return array('result' => 1, 'message' => 'SUCCESS: ITEM DELETED', 'total' => $cart->getTotal());
	}
	
	public function commandCartTotal($parameters)
	{
		$session = $this->getSession($parameters);
		if(!$session) {return array('result' => 0, 'message' => 'ERROR: PLEASE AUTH FIRST');}
		
		$cart = new bagginsRestCart($session['code'], $session['shop_id'], $session['contact_id']);
		return array('result' => 1, 'message' => 'SUCCESS: HERE IS YOUR CART TOTAL', 'total' => $cart->getTotal());
	}
	
	public function commandCartItems($parameters)
	{
		$session_code = ifempty($parameters['session_code'], 0);
		$session_model = new bagginsRestSessionModel();
		$shop_id = $session_model->getPreferredShop($session_code);
		
		$shop_model = new bagginsShopModel();
		$shop_address  = $shop_model->getAddressName($shop_id[0]['shop_id']);

		$session = $this->getSession($parameters);
		if(!$session) {return array('result' => 0, 'message' => 'ERROR: PLEASE AUTH FIRST');}
		
		$cart = new bagginsRestCart($session['code'], $session['shop_id'], $session['contact_id']);
		$items = $cart->getItems();
		if(count($items))
		{
			if(count($items['processed']))
			{
				foreach($items['processed'] as $key => $item)
				{
					$items['processed'][$key]['additions'] = $this->arrayUnKey($item['additions']);
				}
				$items['processed'] = $this->arrayUnKey($items['processed']);
				
				foreach($items['default'] as $key => $item)
				{
					$items['default'][$key]['additions'] = $this->arrayUnKey($item['additions']);
				}
				$items['default'] = $this->arrayUnKey($items['default']);
				
				$items['offers'] = $this->arrayUnKey($items['offers']);
			}
		}
		$shop_data = array(
			'address' => $shop_address[0]['address'],
			'time_now' => '14.54',
			'time_beggin' => '07',
			'time_end' => '19'
		);
		return array('result' => 1, 'message' => 'SUCCESS: HERE ARE YOUR CART ITEMS', 'shop_data' => $shop_data , 'items' => $items);
	}
	
	public function commandOrderCreate($parameters)
	{
		$session = $this->getSession($parameters);
		if(!$session) {return array('result' => 0, 'message' => 'ERROR: PLEASE AUTH FIRST');}
		
		$cart = new bagginsRestCart($session['code'], $session['shop_id'], $session['contact_id']);
		$cart_data = $cart->getItems();
		$items = $cart_data['processed'];
		if(!count($items)) {return array('result' => 0, 'message' => 'ERROR: CART IS EMPTY');}
		
		$order = new bagginsRestOrder();
		$order->setData(
			array(
				'shop_id' => $session['shop_id'],
				'offer_id' => $cart_data['applied_id'],
				'session_code' => $session['code'],
				'customer_id' => $session['contact_id'],
				'status' => 'new',
			)
		);
		$order->setItems($items);
		$order_id = $order->save();
		$cart->clear();
		
		return array('result' => 1, 'message' => 'SUCCESS: ORDER CREATED', 'rest_order_id' => $order_id, 'payment_url' => bagginsRestOrder::getPaymentUrl($order_id));
	}
	
	public function commandSetCustomerData($parameters)
	{
		$session = $this->getSession($parameters);
		if(!$session) {return array('result' => 0, 'message' => 'ERROR: PLEASE AUTH FIRST');}
		
		$contact_id = $session['contact_id'];
		if($contact_id == 0) {return array('result' => 0, 'message' => 'ERROR: NOT ALLOWED FOR ANONIMOUS SESSIONS');}
		
		$contact_model = new waContactModel();
		$contact_data = $contact_model->getById($contact_id);
		if(!$contact_data) {return array('result' => 0, 'message' => 'ERROR: INVALID SESSION CONTACT');}
		
		$contact = new waContact($contact_id);
		
		// ФИО
		if(empty($contact_data['firstname']) && !empty($parameters['firstname'])) {$contact->set('firstname', $parameters['firstname']);}
		if(empty($contact_data['middlename']) && !empty($parameters['middlename'])) {$contact->set('middlename', $parameters['middlename']);}
		if(empty($contact_data['lastname']) && !empty($parameters['lastname'])) {$contact->set('lastname', $parameters['lastname']);}
		// Пол
		if(empty($contact_data['sex']) && !empty($parameters['sex']))
		{
			$sex = $parameters['sex'];
			if(!$sex != 'f') {$sex = 'm';}
			$contact->set('sex', $sex);
		}
		// Дата рождения
		if(empty($contact_data['birth_day']) && empty($contact_data['birth_month']) && empty($contact_data['birth_year']))
		{
			if(!empty($parameters['birth_day']) && !empty($parameters['birth_month']) && !empty($parameters['birth_year']))
			{
				$day = intval($parameters['birth_day']);
				$month = intval($parameters['birth_month']);
				$year = intval($parameters['birth_year']);
				if(checkdate($month, $day, $year))
				{
					$contact->set('birth_day', $day);
					$contact->set('birth_month', $month);
					$contact->set('birth_year', $year);
				}
			}
		}
		$contact->save();
		
		return array('result' => 1, 'message' => 'SUCCESS: CONTACT DATA SET');
	}
	
	public function commandGetCustomerData($parameters)
	{
		$session = $this->getSession($parameters);
		if(!$session) {return array('result' => 0, 'message' => 'ERROR: PLEASE AUTH FIRST');}
		
		$contact_id = $session['contact_id'];
		if($contact_id == 0) {return array('result' => 0, 'message' => 'ERROR: NOT ALLOWED FOR ANONIMOUS SESSIONS');}
		
		$contact_model = new waContactModel();
		$contact_data = $contact_model->getById($contact_id);
		if(!$contact_data) {return array('result' => 0, 'message' => 'ERROR: INVALID SESSION CONTACT');}
		/*Предпочитаемый id кофейни*/
		//убрать за ненадобностью
//		$contact_shops_model = new bagginsCustomerShopsModel();
//		$shop_id = $contact_shops_model->getPreferredShop($contact_data['id']);
		
		$contact_response_data = array(
			'firstname' => $contact_data['firstname'],
			'middlename' => $contact_data['middlename'],
			'lastname' => $contact_data['lastname'],
			'birth_day' => $contact_data['birth_day'],
			'birth_month' => $contact_data['birth_month'],
			'birth_year' => $contact_data['birth_year'],
			'sex' => $contact_data['sex']
		);
		
		return array('result' => 1, 'message' => 'SUCCESS: HERE IS THE DATA', 'contact' => $contact_response_data);
	}
	
	public function commandSetDefaultInvite($parameters)
	{
		$session = $this->getSession($parameters);
		if(!$session) {return array('result' => 0, 'message' => 'ERROR: PLEASE AUTH FIRST');}
		
		$contact_id = $session['contact_id'];
		if($contact_id == 0) {return array('result' => 0, 'message' => 'ERROR: NOT ALLOWED FOR ANONIMOUS SESSIONS');}
		
		$contact_model = new waContactModel();
		$contact_data = $contact_model->getById($contact_id);
		if(!$contact_data) {return array('result' => 0, 'message' => 'ERROR: INVALID SESSION CONTACT');}
		
		$structure_model = new bagginsLinearStructureModel();
		$contact_structure_data = $structure_model->getById($contact_id);
		if($contact_structure_data) {return array('result' => 0, 'message' => 'ERROR: CONTACT ALREADY INVITED');}
		
		bagginsLinearStructureHelper::addContact($contact_id, 1);
		
		return array('result' => 1, 'message' => 'SUCCESS: INVITE SET');
	}
	
	public function commandSetCustomerInvite($parameters)
	{
		$session = $this->getSession($parameters);
		if(!$session) {return array('result' => 0, 'message' => 'ERROR: PLEASE AUTH FIRST');}
		
		$contact_id = $session['contact_id'];
		if($contact_id == 0) {return array('result' => 0, 'message' => 'ERROR: NOT ALLOWED FOR ANONIMOUS SESSIONS');}
		
		$contact_model = new waContactModel();
		$contact_data = $contact_model->getById($contact_id);
		if(!$contact_data) {return array('result' => 0, 'message' => 'ERROR: INVALID SESSION CONTACT');}
		
		$structure_model = new bagginsLinearStructureModel();
		$contact_structure_data = $structure_model->getById($contact_id);
		if($contact_structure_data) {return array('result' => 0, 'message' => 'ERROR: CONTACT ALREADY INVITED');}
		
		$parent_phone = $this->validatePhone(ifempty($parameters['parent'], ''));
		if(!$parent_phone) {return array('result' => 0, 'message' => 'ERROR: ILLEGAL PARENT PHONE');}
		
		$parent_data = $this->getContactDataByPhone($parent_phone);
		if(!$parent_data) {return array('result' => 0, 'message' => 'ERROR: PARENT IS NOT USING AFFILIATE PROGRAM');}
		$parent_structure_data = $structure_model->getById($parent_data['contact_id']);
		if(!$parent_structure_data) {return array('result' => 0, 'message' => 'ERROR: PARENT IS NOT USING AFFILIATE PROGRAM');}
		
		bagginsLinearStructureHelper::addContact($contact_id, $parent_data['contact_id']);
		
		return array('result' => 1, 'message' => 'SUCCESS: INVITE SET');
	}
	
	public function commandGetCustomerInvite($parameters)
	{
		$session = $this->getSession($parameters);
		if(!$session) {return array('result' => 0, 'message' => 'ERROR: PLEASE AUTH FIRST');}
		
		$contact_id = $session['contact_id'];
		if($contact_id == 0) {return array('result' => 0, 'message' => 'ERROR: NOT ALLOWED FOR ANONIMOUS SESSIONS');}
	
		$contact_model = new waContactModel();
		$contact_data = $contact_model->getById($contact_id);
		if(!$contact_data) {return array('result' => 0, 'message' => 'ERROR: INVALID SESSION CONTACT');}
		
		$structure_model = new bagginsLinearStructureModel();
		$contact_structure_data = $structure_model->getById($contact_id);
		if(!$contact_structure_data) {return array('result' => 0, 'message' => 'ERROR: CONTACT IS NOT USING AFFILIATE PROGRAM');}
		
		$parent_id = $contact_structure_data['parent_id'];
		$parent_data = $contact_model->getById($parent_id);
		if(!$parent_data) {return array('result' => 1, 'message' => 'SUCCESS: NO INVITE FOR THIS CONTACT', 'parent' => null);}
		
		$parent = array(
			'name' => $parent_data['name'],
			'phone' => $this->getContactPhoneById($parent_id),
		);
		
		return array('result' => 1, 'message' => 'SUCCESS: HERE IS INVITE FOR THIS CONTACT', 'parent' => $parent);
	}
	
	public function commandTrustedAuth($parameters)
	{
		$verification = $this->verifyUserAgent($parameters);
		if(!$verification['result']) {return $verification;}
		
		// Phone format 79501234567
		$phone = $this->validatePhone(ifempty($parameters['phone'], ''));
		//echo $phone;
		if(!$phone) {return array('result' => 0, 'message' => 'ERROR: INVALID PHONE');}
		
		$contact_data_model = new waContactDataModel();
		$contact_data = $contact_data_model->query("SELECT * FROM ".$contact_data_model->getTableName()."
													WHERE field = 'phone' AND value = s:phone ORDER BY contact_id ASC LIMIT 1", array('phone' => $phone))->fetchAll();
		if(!count($contact_data)) {$contact_data = null;} else {$contact_data = $contact_data[0];}
		if(!$contact_data)
		{
			$contact = new waContact();
			$contact->set('phone', $phone);
			$contact->save();
			$contact_id = $contact->getId();
		}
		else
		{
			$contact_id = $contact_data['contact_id'];
		}
		
		$session_model = new bagginsRestSessionModel();
		$session_code = $session_model->createTrustedSession($contact_id, $phone);
		$session_data = $session_model->getById($session_code);
		if(!$session_data) {return array('result' => 0, 'message' => 'ERROR: COULD NOT CREATE SESSION');}
		
		return array('result' => 1, 'message' => 'SUCCESS: HERE IS YOUR SESSION', 'session_code' => $session_data['code']);
	}
	
	public function commandGetCustomerStructure($parameters)
	{
		$session = $this->getSession($parameters);
		if(!$session) {return array('result' => 0, 'message' => 'ERROR: PLEASE AUTH FIRST');}
		
		$contact_id = $session['contact_id'];
		if($contact_id == 0) {return array('result' => 0, 'message' => 'ERROR: NOT ALLOWED FOR ANONIMOUS SESSIONS');}
	
		$contact_model = new waContactModel();
		$contact_data = $contact_model->getById($contact_id);
		if(!$contact_data) {return array('result' => 0, 'message' => 'ERROR: INVALID SESSION CONTACT');}
		
		$structure_model = new bagginsLinearStructureModel();
		$contact_structure_data = $structure_model->getById($contact_id);
		if(!$contact_structure_data) {return array('result' => 0, 'message' => 'ERROR: CONTACT IS NOT USING AFFILIATE PROGRAM');}
		
		$children = $structure_model->getFullChildrenAPI($contact_structure_data);
		
		return array('result' => 1, 'message' => 'SUCCESS: HERE IS THE STRUCTURE', 'children' => $this->arrayUnKey($children));
	}
	
	public function commandGetCustomerAccount($parameters)
	{
		$session = $this->getSession($parameters);
		if(!$session) {return array('result' => 0, 'message' => 'ERROR: PLEASE AUTH FIRST');}
		
		$contact_id = $session['contact_id'];
		if($contact_id == 0) {return array('result' => 0, 'message' => 'ERROR: NOT ALLOWED FOR ANONIMOUS SESSIONS');}
	
		$contact_model = new waContactModel();
		$contact_data = $contact_model->getById($contact_id);
		if(!$contact_data) {return array('result' => 0, 'message' => 'ERROR: INVALID SESSION CONTACT');}
		
		$account = new bagginsCustomerAccount($contact_id);
		return array('result' => 1, 'message' => 'SUCCESS: HERE IS THE AMOUNT', 'amount' => $account->getAmount());
	}
	
	public function isSessionValid($parameters)
	{
		$session = $this->getSession($parameters);
		if(!$session) {return array('result' => 0, 'message' => 'ERROR: SESSION NOT VALID');}
		return array('result' => 1, 'message' => 'SUCCESS: SESSION IS VALID');
	}
	
	public function commandSysTime($parameters)
	{
		return array('result' => 1, 'message' => 'SUCCESS: HERE IS THE TIME', 'timestamp' => time()-3, 'datetime' => date('Y-m-d H:i:s', (time()-3)));
	}
	
	/////////////////////////////////////////////////////////////////////////////////////////////
	// SERVICE
	/////////////////////////////////////////////////////////////////////////////////////////////

	public function getSession($parameters)
	{
		$code = ifempty($parameters['session_code'], 0);
		$session_model = new bagginsRestSessionModel();
		return $session_model->getById($code);
	}

	public function verifyUserAgent($parameters)
	{
		if(!isset($parameters['s'])) {return array('result' => 0, 'message' => 'ERROR: SIGNATURE NOT FOUND');}
		if(!isset($parameters['t'])) {return array('result' => 0, 'message' => 'ERROR: TIMESTAMP NOT FOUND '.time());}
		if(!isset($parameters['ua'])) {return array('result' => 0, 'message' => 'ERROR: UA NOT FOUND');}
		
		$timestamp = intval($parameters['t']);
		$time = time();
		if($timestamp < $time - 7200 || $timestamp > $time + 7200) {return array('result' => 0, 'message' => 'ERROR: INVALID TIMESTAMP');}
		
		$agent_model = new bagginsRestAgentModel();
		$agent_data = $agent_model->getByUserAgent($parameters['ua']);
		if(!$agent_data) {return array('result' => 0, 'message' => 'ERROR: UA NOT VALID');}
		
		$request_data = $parameters;
		unset($request_data['s']);
		ksort($request_data);
		
		$signature = '';
		foreach($request_data as $key => $field) {$signature .= $field;}
		$signature .= $agent_data['secret_key'];
		if($parameters['s'] != md5($signature)) {return array('result' => 0, 'message' => 'ERROR: INVALID SIGNATURE SHOULD BE '.md5($signature));}
		
		return array('result' => 1, 'message' => 'SUCCESS: SIGNATURE IS VALID');
	}
	
	public function validatePhone($phone)
	{
		$phone = preg_replace("/[^0-9]/", '', $phone);
		if(mb_strlen($phone) != 11) {return null;}
		if(mb_substr($phone, 1, 1) != '9') {return null;}
		
		return $phone;
	}
	
	public function getContactDataByPhone($phone)
	{
		$phone = mb_substr($phone, 1);
		$contact_data_model = new waContactDataModel();
		$contact_data = $contact_data_model->query("SELECT * FROM ".$contact_data_model->getTableName()."
													WHERE field = 'phone' AND value LIKE '%".$contact_data_model->escape($phone)."%' ORDER BY contact_id ASC LIMIT 1")->fetchAll();
		if(!count($contact_data)) {return null;}
		return $contact_data[0];
	}
	
	public function getContactPhoneById($contact_id)
	{
		$contact_data_model = new waContactDataModel();
		$contact_data = $contact_data_model->query("SELECT * FROM ".$contact_data_model->getTableName()."
													WHERE field = 'phone' AND contact_id = i:contact_id ORDER BY value ASC LIMIT 1", array('contact_id' => $contact_id))->fetchAll();
		if(!count($contact_data)) {return null;}
		return $contact_data[0]['value'];
	}
	
	public function arrayUnKey($array)
	{
		if(!count($array)) {return array();}
		$result = array();
		foreach($array as $key => $value) {array_push($result, $value);}
		return $result;
	}
	/*MAX*/
	public function commandGetProductAdditions($parameters)
	{
		$shop_id = intval(ifempty($parameters['shop_id'], 0));
		$product_id = intval(ifempty($parameters['product_id'], 0));
		$rest_category_products_model = new bagginsRestCategoryProductsModel();
		$result = $this->arrayUnKey($rest_category_products_model->getAPIAdditions($shop_id, $product_id));
		if(count($result))
		{
			return array ('result' => 1, 'additions' => $result);
		}
		else
		{
			return array ('result' => 0, 'additions' => $result);
		}
	}
	
	public function commandGetPrefferredShop($parameters)
	{
		$session_code = ifempty($parameters['session_code'], 0);
		$session_model = new bagginsRestSessionModel();
		$shop_id = $session_model->getPreferredShop($session_code);
		if($shop_id[0]['shop_id'])
		{
			return array("shop_id" => $shop_id[0]['shop_id']);
		}
		else
		{
			return array("shop_id" => null);
		}
	}
	
	public function commandMergeSession($parameters)
	{
		$session_anonim = ifempty($parameters['session_anonim'], 0);
		$session_code = ifempty($parameters['session_code'], 0);
		$session_model = new bagginsRestSessionModel();
		$session_model->mergeSession($session_anonim,$session_code);
		$rest_order_model = new bagginsRestOrderModel();
		$rest_order_model->updateByField('session_code', $session_anonim, array('session_code' => $session_code));;
	}
	
	public function commandCheckInternet()
	{
		return "1";
	}

/*	public function commandPaySberbabkCard($parameters)
	{
//        $parameters['session_code'] = ifempty($parameters['session_code'], 0);
//        $parameters['token'] = ifempty($parameters['token'], 0);

        $this->comandSberbankToken($parameters);
	}*/

	public function requestToPhone($fields)
	{
		try {
			define( 'API_ACCESS_KEY', 'AAAARzal88c:APA91bEujHsBNTMpjm3G95LIoGw7jrm42xKWlb-Z409b4n4RVLhr9qxr8MTf1rJyCj_OAa7nsj-gOOZRfwyBlOUdytpY6yZvrFi3wKeQTw10LSs_xwWc06w9hq5f_LmlewNtWMG8Ervz');

			$headers = array
			(
				'Authorization: key=' . API_ACCESS_KEY,
				'Content-Type: application/json'
			);
			#Send Reponse To FireBase Server
			$ch = curl_init();
			curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
			curl_setopt( $ch,CURLOPT_POST, true );
			curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
			curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
			$result = curl_exec($ch );
			curl_close( $ch );
		}
		catch (Exception $e){
            $result = $e->getMessage();
        }
		file_put_contents('requestToPhone.txt', $result, FILE_APPEND);
		return $result;
	}
	
	public function commandPaySberbabkCard($parameters)
	{
		$pay_answer = $this->paymentConfirmation($parameters);
		$token_phone = ifempty($parameters['token'], 0);
//		$time = $parameters['time'];
		//$token_phone = "eKjnsEUAeWg:APA91bEcnmVR0QlIQBlNEDpviAg5A3NA1MaurdS_Gq-MbIlUYR07LJB7yFkd1nZ-rVeEq36kSyPYXSOco5CfRiwdlndD1AzMmDzIkN7YNtwJ6Rrtc01NpGy0xk54lrRSkL1bo3WQgHsh";
		$data = array
		(
			'data' => 'SBERBANK_TOKEN_PAY',
			'result' => $pay_answer['result'],
			'message' => $pay_answer['message'],
		);

		$fields = array
		(
			'to'		=> $token_phone,
			'data' => $data,
		);
		$this->requestToPhone($fields);
	}

	// TODO : gurianoff
    public function getTokenByRestOrder($order_id)
    {
        $stsber_order = new bagginsStsberOrderModel();
        $plugin_order = $stsber_order->getByField('rest_order_id', $order_id);
        if ($plugin_order)
            return $plugin_order['order_data'];
        else
            return null;
    }
	public function paymentConfirmation($params){
        /**
         * @params string session_code
         * @params string token
         */
        $payment_id = 1; //заглушка ибо только сбер
	    // по сессии берем сумму корзины
        $rest_session = new bagginsRestSessionModel();
        $session = $rest_session->getById($params['session_code']);
        if(!$session)
            return array('result' => 0, 'message' => "Сессия не найдена");

        $cart = new bagginsRestCart($session['code'], $session['shop_id'], $session['contact_id']);
        $total = $cart->getTotal();
        if(!$total || $total<=0)
            return array('result' => 0, 'message' => "В корзине нет товаров или сумма заказа должна быть больше нуля");

        // Проверка плагина оплаты
        $plugin_model = new bagginsPluginModel();
        $plugin_info = $plugin_model->getById($payment_id);
        if(!$plugin_info) {array_push($errors, 'Некорректный метод оплаты');}
        else {
            if($plugin_info['type'] != bagginsPluginModel::TYPE_PAYMENT)
            {return array('result' => 0, 'message' => "Некорректный метод оплаты");}
        }

        // Проверка суммы платежа
        if($total < 1 || $total > 10000) {return array('result' => 0, 'message' => "Сумма должна быть в пределах от 1 до 10 000 руб.");}

        $stsber_order_model = new bagginsStsberOrderModel();
        $stsber_order_id = $stsber_order_model->insert(array(
            'contact_id' => $session['contact_id'],
            'service' => $params['session_code'],
            'amount' => $total,
            'payment_id' => $payment_id,
            'order_data' => $params['token'],
            'create_datetime' => date('Y-m-d H:i:s'),
            'paid_datetime' => NULL,
            'time_give_out' => $params['time']
        ));
        if (!$stsber_order_id)
            {return array('result' => 0, 'message' => "Ошибка регистрации заказа");}
        $order_data = array(
            'id' => $stsber_order_id,
            'contact_id' => $session['contact_id'],
            'total' => $total,
            'currency' => 'RUB',
            'data' => array(
                'datetime' => date('Y-m-d H:i:s')
            )
        );
        try
        {
            $plugin = bagginsPayment::getPlugin(null, $payment_id);
            $payment = $plugin->payment(null, $order_data, false);
        }
        catch (waException $ex) {$payment = $ex->getMessage();}
        return array('result' => 1, 'message' => $payment);
    }

	public function pushNotificationToPhone($token, $title, $body)
	{
		$data_notification = array
		(
			'title' 	=> $title,
			'body'	=> $body,
		);

		$fields_notification = array
		(
			'to'		=> $token,
			'notification' => $data_notification,
		);
		$this->requestToPhone($fields_notification);
	}
	
    public function paymentResult($params){
		$result = 0;
		$confirmation_code = 0;
		if($params['message'] == "Success payment!")
		{
			$result = 1;
            $confirmation_code = $params['confirm'];
		}
		$rest_order_model = new bagginsRestOrderModel();
		$order_number = $rest_order_model->getOrderNumber($params['order_id']);
        $data = array
        (
			'data' => 'paymentResult',
			'result' => $result,
            'order_id' => $params['order_id'],
            'order_number' => $params['order_id'],
        );

        $fields = array
        (
            'to'		=> $params['token'],
            'data' => $data,
        );
		
		$title = "Заказ №".$params['order_id']." готовится";
		$body = "Код подтверждения заказа ".$confirmation_code;
		$this->pushNotificationToPhone($params['token'], $title, $body);

        $this->requestToPhone($fields);
    }
	
    public function orderToggle($order_id, $status){
	    $token = $this->getTokenByRestOrder($order_id);
	    if (!$token)
	        return;
		
		$title_status = "";
		if($status == "ready")
		{
			$title_status = "ГОТОВ";
		}
		if($status == "deployed")
		{
			$title_status = "ВЫДАН";
		}
		$rest_order_model = new bagginsRestOrderModel();
		$order_number = $rest_order_model->getOrderNumber($order_id);
		
		$title = "Заказ №".$order_id;
		$body = "Имеет стататус ".$title_status;
		
		$data = array
        (
			'data' => 'updateStatusOrder',
			'status' => $status,
			'result' => '1',
            'order_id' => $order_id, //rest_order_id
        );

        $fields = array
        (
            'to'		=> $token,
            'data' => $data,
        );
		file_put_contents('orderToggle.txt', $fields, FILE_APPEND);
		$this->pushNotificationToPhone($token, $title, $body);
		$this->requestToPhone($fields);
    }
	
    public function getOrdersBySession($parameters)
    {
		$session_code = ifempty($parameters['session_code'], 0);
        $rest_order_model = new bagginsRestOrderModel();
        $orders = $rest_order_model->getAllOrdersBySession($session_code);
        if(count($orders))
		{
			$result = 1;
		}
		else
		{
			$result = 0;
		}
		return array('result' => $result, 'data_orders' => $this->arrayUnKey($orders));
    } 
    public function getFullOrderInfo($parameters)
    {
		$session_code = ifempty($parameters['session_code'], 0);
		$rest_order_id = $parameters['order_id'];
        $rest_order_model = new bagginsRestOrderModel();
        $order = $rest_order_model->getOrderInfo($rest_order_id, $session_code);
		$order['items_order'] = $this->arrayUnKey($order['items_order']);
		foreach($order['items_order'] as $key => $item)
		{
			$order['items_order'][$key]['additions']= $this->arrayUnKey($order['items_order'][$key]['additions']); 
		}
	    return $order;
    }

}
