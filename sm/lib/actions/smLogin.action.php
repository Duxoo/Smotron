<?php

class smLoginAction extends waLoginAction
{

    public function execute()
    {
        $method = waRequest::getMethod();
        if($method == 'get') {
            $this->setLayout(new smFrontendLayout());
            $this->setThemeTemplate('login.html');
            $this->view->assign('method', 'get');
            try {
                parent::execute();
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
        else{
            $this->setLayout(null);
            $this->view->assign('method', 'post');
            $this->setThemeTemplate('login.html');

            $auth_data['login'] = waRequest::post('email', '', 'string');
            $auth_data['password'] = waRequest::post('password', '', 'string');
            //if(empty($auth_data['login'])){ $this->response = array('result' => 0, 'message' => 'start action'); return; };

            $auth = new waAuth();

            $contact = $auth->getByLogin($auth_data['login']);
            if ($contact)
            {
                if ($contact["password"] == md5($auth_data['password'])) {
                    try
                    {
                        $auth->auth($auth_data);
                        $this->response = array('result' => 1);
                        return;
                    }
                    catch(waException $e)
                    {
                        $this->response = array('result' => 0, 'message' => $e->getMessage());
                        return;
                    }
                }
            }
        }


        /*try {
            parent::execute();
        } catch (waException $e) {
            if ($e->getCode() == 404) {
                $this->view->assign('error_code', $e->getCode());
                $this->view->assign('error_message', $e->getMessage());
                $this->setThemeTemplate('error.html');
            } else {
                throw $e;
            }
        }*/
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
        $url = '/smotron/';
        $this->redirect($url);
    }

}
