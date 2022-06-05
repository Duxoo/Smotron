<?php

class smLoginController extends waJsonController
{
    public function execute()
    {
        if(!waRequest::isXMLHttpRequest()){ wa()->getResponse()->redirect('/?ar=1', 301); }

        $auth_data['login'] = waRequest::post('email', '', 'string');
        $auth_data['password'] = waRequest::post('password', '', 'string');

        if(!$auth_data['login']){$this->response = array('result' => 0, 'message' => 'Заполните поле e-mail!');return;}
        if(!$auth_data['password']){$this->response = array('result' => 0, 'message' => 'Заполните поле пароль!');return;}

        $regexp = '/^U100[0-9]{1,}_[A-Za-z0-9]{1,}/';
        if(preg_match($regexp,$auth_data['login']))
        {
            waLog::dump('t');
            $helper = new smHelper();
            $subuser = $helper->getSubuserByLogin($auth_data['login']);
            $sub_auth = new smAuth();
            if($helper::getPasswordHash($auth_data['password']) == $subuser['password'])
            {
                waLog::dump('te');
                $sub_auth->subAuth($auth_data);
                $this->response = array('result' => 2, 'url' => '/stream/');
                return;
            }
            else{$this->response = array('result' => 0, 'message' => 'Неверный e-mail или пароль!');return;}
        }

        $auth = new waAuth();
        $contact = $auth->getByLogin($auth_data['login']);
        waLog::dump($contact);

        if(!isset($contact)){$this->response = array('result' => 0, 'message' => 'Неверный логин или пароль!');return;}

        if ($contact["password"] == md5($auth_data['password'])) {
            try{
                $auth->auth($auth_data);
                setcookie('uuid', "",time()-36000,'/');   //удаление куков сабпользователя
                setcookie('login', "",time()-36000,'/');
                $this->response = array('result' => 1, 'url' => '/my/profile/');
                return;
            }
            catch(waException $e){waLog::dump('catch');$this->response = array('result' => 0, 'message' => $e->getMessage());return;}
        }

    }

}