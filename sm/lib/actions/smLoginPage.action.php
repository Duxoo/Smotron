<?php

class smLoginPageAction extends waLoginAction
{

    public function execute()
    {
        $this->setLayout(new smFrontendLayout());
        $this->setThemeTemplate('login.html');
        try {
            $auth_data['login'] = waRequest::post('email', '', 'string');
            $auth_data['password'] = waRequest::post('password', '', 'string');

            if(!$auth_data['login']){$this->response = array('result' => 0, 'message' => 'Заполните поле e-mail!');return;}
            if(!$auth_data['password']){$this->response = array('result' => 0, 'message' => 'Заполните поле пароль!');return;}

            $regexp = '/^U100[0-9]{1,}_[A-Za-z0-9]{1,}/';
            if(preg_match($regexp,$auth_data['login']))
            {
                $helper = new smHelper();
                $subuser = $helper->getSubuserByLogin($auth_data['login']);
                $sub_auth = new smAuth();
                if($helper::getPasswordHash($auth_data['password']) == $subuser['password'])
                {
                    $sub_auth->subAuth($auth_data);
                    $this->response = array('result' => 2, 'url' => '/stream/');
                    return;
                }
                else{$this->response = array('result' => 0, 'message' => 'Неверный e-mail или пароль!');return;}
            }
            $auth = new waAuth();
            $contact = $auth->getByLogin($auth_data['login']);
            if(!isset($contact)){$this->response = array('result' => 0, 'message' => 'Неверный e-mail или пароль!');return;}
            if ($contact["password"] == md5($auth_data['password'])) {
                try {$auth->auth($auth_data);$this->response = array('result' => 1, 'url' => '/my/profile/');return;}
                catch(waException $e) {$this->response = array('result' => 0, 'message' => $e->getMessage());return;}
            }
        } catch (waException $e) {
            if ($e->getCode() == 404) {
                $this->view->assign('error_code', $e->getCode());
                $this->view->assign('error_message', $e->getMessage());
                $this->setThemeTemplate('error.html');
            } else {
                throw $e;
            }
        }
    }

    protected function redirectAfterAuth()
    {
        if (waRequest::get('return')) {
            $url = $this->getStorage()->get('auth_referer');
            if ($url) {
                $this->getStorage()->del('auth_referer');
                $this->redirect($url);
            }
        }
        $this->getStorage()->del('auth_referer');
        $url = wa()->getRouteUrl('sm/frontend/myProfile');/*'/smotron/'*/
        waLog::dump($url);
        $this->redirect($url);
    }

}
