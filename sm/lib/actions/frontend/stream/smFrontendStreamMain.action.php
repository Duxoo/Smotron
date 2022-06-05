<?php

class smFrontendStreamMainAction extends waViewAction
{
    public function execute()
    {
        //$_SESSION
        $auth = new smAuth();

        if($auth->isSubAuth())
        {
            waLog::dump($auth->isSubAuth());
            $this->setThemeTemplate('stream.html');

            $login = waRequest::param('sub_user_login', 0,'string');

            $helper = new smHelper();
            $subuser_id = $helper->getSubuserByLogin($login);
            $channels = $helper->getChannels($subuser_id);

            $this->view->assign('channels', $channels);
        }
        else
        {
            $this->setThemeTemplate('error.html');
            $this->setLayout(new smFrontendLayout());
            $this->view->assign('error', 'Вы не авторизованы');
        }
    }
}