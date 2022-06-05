<?php

class smSignupController extends waJsonController
{
    public function execute()
    {
        if(!wa()->getCaptcha()->isValid()) {$this->response = array('result' => 0, 'message' => 'Поставьте галочку "Я не робот"'); return;}
        if(wa()->getUser()->isAuth()) {$this->response = array('result' => 0, 'message' => 'Вы уже в системе'); return;}


        $referral_code = waRequest::cookie("ref","","string");

        //$terms = waRequest::post('terms', '', 'string');
        //if(!mb_strlen($terms)) {$this->response = array('result' => 0, 'message' => 'Вы не приняли условия пользовательского соглашения'); return;}

        $name = trim(waRequest::post('name', '', 'string'));
        $email = waRequest::post('email', '', 'string');
        $promocode = trim(waRequest::post('promocode', '', 'string'));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {$this->response = array('result' => 0, 'message' => 'Неправильный формат e-mail', 'email' => 1); return;}

        if(!$email){$this->response = array('result' => 0, 'message' => 'Заполните поле e-mail!'); return;}

        $helper = new smHelper();

        waLog::dump($promocode, 'ddd.log');
        waLog::dump($referral_code, 'ddd.log');

        $registration = $helper->registerContact($email, $name, $promocode, $referral_code);
        if($registration['result'] == 0) {$this->response = $registration; return;}
        $auth_data = array();
        $auth_data['login'] = $email;
        $auth_data['password'] = $registration['password'];

        $_POST['remember'] = 0;
        $auth = wa()->getAuth();
        waLog::dump($auth_data,'auth.log');

        try
        {
            $auth->auth($auth_data);
        }
        catch(waException $e)
        {
            $this->response = array('result' => 0, 'message' => $e->getMessage());
            return;
        }
        if(isset($registration))
        {
            waLog::dump(array(
                '{LOCALE}'       => wa()->getLocale(),
                '{CONTACT_NAME}' => htmlentities(wa()->getUser()->getName(),ENT_QUOTES,'utf-8'),
                '{EMAIL}'        => $email,
                '{COMPANY}'      => 'СМОТРОН.ТВ',
                '{PASSWORD}'     => $registration['password'],
                '{DOMAIN}'       => waRequest::server('HTTP_HOST'),
            ),'sm/registration/registration_data.log');

            smHelper::sendEmailSimpleTemplate(
                $email,
                'welcome_signup',
                array(
                    '{LOCALE}'       => wa()->getLocale(),
                    '{CONTACT_NAME}' => htmlentities(wa()->getUser()->getName(),ENT_QUOTES,'utf-8'),
                    '{EMAIL}'        => $email,
                    '{COMPANY}'      => 'СМОТРОН.ТВ',
                    '{PASSWORD}'     => $registration['password'],
                    '{DOMAIN}'       => waRequest::server('HTTP_HOST'),
                ) // , wa()->getUser()->get('email', 'default')
            );
        }
        unset($registration['contact_id']);
        unset($registration['password']);
        $this->response = $registration;

    }

    public function afterSignup(waContact $contact)
    {
        $contact->addToCategory($this->getAppId());
    }
}