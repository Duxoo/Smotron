<?php

class smHelper
{
    public function registerContact($email, $name = null, $promocode = null, $referral_code = null)
    {
        if($email === null){ return 0; }
        if($promocode != null or $referral_code != null){
            if((substr($promocode, 0, 3) == 'r35') or (substr($referral_code, 0, 3) == 'r35')){}
            else{
                $promocode_model = new smPromocodeModel();
                $promo = $promocode_model->getByField('promocode', $promocode);
                $promoc = new smPromocode($promo['id']);
                if($promoc->get('count') < 1) { return array('result' => '0', 'message' =>'Промокод закончился'); }
                if($promoc->get('datetime_end') < date('Y-m-d')) { return array('result' => '0', 'message' =>'Истек срок действия промокода'); }
            }
        }
        /*$waSignup = new waSignupAction();
        $data['email'] = $email;
        $result = $waSignup->signup($data);
        waLog::dump($result[1]['contact']->get('password'),'signup.log');
        $result['password'] = $result[1]['contact']->get('password');*/

        $contact = new waContact();

        //$user = new waUser();

        $waContactModel = new waContactModel();
        $cont = $waContactModel->getByEmail($email);

        if(empty($cont))
        {
            if( ifempty($name)|| ($name == '')){$name = explode("@",$email); $name = $name[0];}
            $contact['name'] = $name;
            $contact['firstname'] = $name;
            $contact['email'] = $email;
            $pass = $this->generatePassword();
            $contact['password'] = $pass;
            $contact->save();
            if(!empty($referal)){ }
        }else{
            if(empty($cont['password']))
            {
                $contact = new waContact($cont['id']);
                $pass = $this->generatePassword();
                $contact['password'] = $pass;
                $contact->save();
                if(!empty($referal)){ }
            }
            else
            {
                return array('result' => '0', 'message' =>'Данный email уже зарегестрирован');
            }
        }

        waLog::dump($contact);

        // create flussonic User
        $token = new smToken();
        $contact['sm_user_id'] = $contact->getId();
        $token_create = $token->createUserToken($contact);
        waLog::dump($token_create,'token_create.log');
        /*$api = new smFlussonicApi();
        $user_params = array(
            'name' => $contact['name'],
            'email' => $contact['email'],
            'max_sessions' => 5,
        );
        $token = $api->createUser($user_params);
        $token = new smToken();*/

        $sm_user = new smUser($contact->getId());

        if((substr($promocode, 0, 3) == 'r35') or (substr($referral_code, 0, 3) == 'r35'))
        {
            $referral_model = new smReferralModel();
            $row_code = $referral_model->getByField('referral_code', $referral_code);
            $row_promo = $referral_model->getByField('referral_code', $promocode);
            if(isset($row_code)) { $this->referralCreate($contact->getId(), $referral_code); }
            elseif(isset($row_promo)){$this->referralCreate($contact->getId(), $promocode); }
            else {$this->referralCreate($contact->getId()); }
        }
        else
        {
            /*$promocode_model = new smPromocodeModel();
            $promo = $promocode_model->getByField('promocode', $promocode);*/
            if(isset($promo))
            {
                $sm_user->activateSubscribtion($promo['tariff_id'], $promo['tariff_days'],1, $promo['promocode']);
                $sm_user->set('promo',$promo['id']);
                $sm_user->save();
                $promoc->set('count', $promoc->get('count') - 1 );
                $promoc->save();
            }
            $this->referralCreate($contact->getId());
        }

        return array('result' => '1', 'contact_id' =>$contact->getId(), 'password' => $pass, 'message' => 'Ваш пароль: '.$pass);
    }

    public function referralCreate($contact_id, $code = null)
    {
        if(empty($contact_id)){ $contact_id = wa()->getUser()->getId(); }
        $referral = new smReferral($contact_id, $code);
        waLog::dump($contact_id);
        $referral->createNode();
    }

    public function generatePassword()
    {
        $length = 7;
        $alphabet = "abcdefghijklmnopqrstuvwxyz1234567890";
        $result = '';
        while(strlen($result) < $length) {$result .= $alphabet{mt_rand(0, strlen($alphabet)-1)};}
        return $result;
    }

    public function getSubuserByLogin($login)
    {
        $model = new smSubuserModel();
        return $model->getByField('login',$login);
    }

    public function getDomain()
    {
        return 'ivan.res-engineering.ru/';
    }

    public static function getPasswordHash($password)
    {
        $auth = new smAuth();
        return $auth::getPasswordHash($password);
    }

    public function getChannels($id)
    {
        $model = new smSubuserChannelsModel();
        $result =  $model->getSubuserChannels($id);
        $channels = array();
        foreach ($result as $key => $channel)
        {
            array_push($channels, $channel['channel']);
        }
        return $channels;
    }

    public function getAllChannels()
    {
        $api = new smFlussonicApi();
        return $api->getMedia();
    }

    public function getContactTariff($contact_id = null)
    {
        if($contact_id === null){return null;}
        $model = new smContactTariffModel();
        return $model->getById($contact_id);
    }

    public function translit($value)
    {
        $converter = array(
            'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
            'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
            'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
            'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
            'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
            'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
            'э' => 'e',    'ю' => 'yu',   'я' => 'ya',

            'А' => 'A',    'Б' => 'B',    'В' => 'V',    'Г' => 'G',    'Д' => 'D',
            'Е' => 'E',    'Ё' => 'E',    'Ж' => 'Zh',   'З' => 'Z',    'И' => 'I',
            'Й' => 'Y',    'К' => 'K',    'Л' => 'L',    'М' => 'M',    'Н' => 'N',
            'О' => 'O',    'П' => 'P',    'Р' => 'R',    'С' => 'S',    'Т' => 'T',
            'У' => 'U',    'Ф' => 'F',    'Х' => 'H',    'Ц' => 'C',    'Ч' => 'Ch',
            'Ш' => 'Sh',   'Щ' => 'Sch',  'Ь' => '',     'Ы' => 'Y',    'Ъ' => '',
            'Э' => 'E',    'Ю' => 'Yu',   'Я' => 'Ya',   ' ' => '_',
        );

        $value = strtr($value, $converter);
        return $value;
    }

	/**
     * Returns HTML code of a Webasyst icon.
     *
     * @param string|null $icon Icon type
     * @param string|null $default Default icon type to be used if $icon is empty.
     * @param int $size Icon size in pixels. Available sizes: 10, 16.
     * @param array $params Extra parameters:
     *     'class' => class name tp be added to icon's HTML code
     * @return string
     */
    public static function getIcon($icon, $default = null, $size = 16, $params = array())
    {
        if (!$icon && $default) {$icon = $default;}
        $class = isset($params['class']) ? ' '.htmlentities($params['class'], ENT_QUOTES, 'utf-8') : '';

        if ($icon)
		{
            if (preg_match('/^icon\.([\d\w_\-]+)$/', $icon, $matches))
			{
                $size = ($size == 16) ? 16 : 10;
                $icon = "<i class='icon{$size} {$matches[1]}{$class}'></i>";
            }
			elseif (preg_match('@[\\/]+@', $icon))
			{
                $size = max(10, min(16, $size));
                $icon = "<i class='icon{$size} {$class}' style='background: url({$icon})'></i>";
            }
			else
			{
                $size = ($size == 16) ? 16 : 10;
                $icon = "<i class='icon{$size} {$icon}{$class}'></i>";
            }
        }
        return $icon;
    }
	
	/**
     * Returns array of payment methods.
     *
     * @param array $order Array of order data whose parameters must be pre-filled in payment method's custom fields.
     * @return array
     */
    public static function getPaymentMethods($order = array())
    {
        $plugin_model = new smPluginModel();
        $methods = $plugin_model->listPlugins(smPluginModel::TYPE_PAYMENT);
        $order_params = $order ? $order['params'] : array();
        $order = new waOrder(
            array(
                'contact_id' => $order ? $order['contact_id'] : null,
                'contact'    => $order ? new waContact($order['contact_id']) : null,
                'params'     => $order_params
            )
        );
        foreach ($methods as $m_id => $m)
		{
            $plugin = smPayment::getPlugin($m['plugin'], $m['id']);
            $custom_fields = $plugin->customFields($order);
            if ($custom_fields)
			{
                $params = array();
                $params['namespace'] = 'payment_'.$m['id'];
                $params['title_wrapper'] = '%s';
                $params['description_wrapper'] = '<br><span class="hint">%s</span>';
                $params['control_wrapper'] = '<div class="name">%s</div><div class="value">%s %s</div>';

                $controls = array();
                foreach ($custom_fields as $name => $row)
				{
                    $row = array_merge($row, $params);
                    if (!empty($order_params['payment_id']) && ($m['id'] == $order_params['payment_id']) && isset($order_params['payment_params_'.$name]))
					{
                        $row['value'] = $order_params['payment_params_'.$name];
                    }
                    if (!empty($row['control_type']))
					{
                        $controls[$name] = waHtmlControl::getControl($row['control_type'], $name, $row);
                    }
                }
                if ($controls)
				{
                    $custom_html = '';
                    foreach ($controls as $c) {$custom_html .= '<div class="field">'.$c.'</div>';}
                    $methods[$m_id]['custom_html'] = $custom_html;
                }
            }
        }
        return $methods;
    }
	
	 /**
     * Returns unavailable payment methods for specified shipping method or shipping methods for which specified payment method is unavailable.
     *
     * @param string $type Method type for which other type will be considered as complimentary; acceptable values: 'payment' or 'shipping'
     * @param int $id Id of method for which methods of other type must be returned
     * @return array Method ids
     */
    public static function getDisabledMethods($type, $id)
    {
        $map = wa()->getSetting('shipping_payment_disabled', null, 'sm');
        if (!$map) {return array();}
        $result = array();
        $map = json_decode($map, true);
        if (is_array($map))
		{
            $complementary = ($type == smPluginModel::TYPE_PAYMENT) ? smPluginModel::TYPE_SHIPPING : smPluginModel::TYPE_PAYMENT;
            if ($complementary == smPluginModel::TYPE_PAYMENT)
			{
                $result = isset($map[$id]) ? $map[$id] : array();
            }
			else
			{
                foreach ($map as $plugin_id => $values)
				{
                    if (in_array($id, $values)) {$result[] = $plugin_id;}
                }
            }
        }
        return $result;
    }

    public static function sendEmailSimpleTemplate($address, $template_id, $vars, $from = null)
    {
        if (!$address || !$template_id || waConfig::get('is_template')) {
            return false;
        }
        $format = 'text/html';
        if (empty($vars['{LOCALE}'])) {
            $vars['{LOCALE}'] = 'en_US';
        }
        $locale = $vars['{LOCALE}'];

        $template_file = wa()->getAppPath().'/templates/messages/'.$template_id.'.'.$locale.'.html';

        // Look for template in appropriate locale
        if (!is_readable($template_file)) {
            throw new waException('Mail template file is not readable: '.$template_file);
        }

        // Load template and replace $vars
        $message = @file_get_contents($template_file);
        $message = explode('{SEPARATOR}', $message);
        if (empty($message[1])) {
            throw new waException('Bad template file format: '.$template_file);
        }
        $subject = trim($message[0]);
        $content = trim($message[1]);

        $subject = str_replace(array_keys($vars), array_values($vars), $subject);
        $content = str_replace(array_keys($vars), array_values($vars), $content);

        // Send message
        $res = true;
        try {
            $mailer = new waMailMessage($subject, $content, $format);
            $mailer->setTo($address);
            if ($from) {
                $mailer->setFrom($from);
            }
            if (!$mailer->send()) {
                $res = false;
            }
        } catch (Exception $e) {
            $res = false;
        }
        return $res;
    }

    public function checkPromocode($promocode)
    {
        return false;
    }
}