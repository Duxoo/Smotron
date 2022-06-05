<?php

class smFrontendStreamChannelsAction extends waViewAction
{
    public function execute()
    {
        //$_SESSION
        $auth = new smAuth();
        $auth_res = $auth->isSubAuth();

        if($auth_res['result'] != true)
        {
            $this->setThemeTemplate('blank.html');
            wa()->getResponse()->redirect(wa()->getRouteUrl('sm/frontend').'?ar=1', 302);
            return;
        }

        $this->setThemeTemplate('channels.html');
        $this->setLayout(new smFrontendLayout());
        $this->getResponse()->setTitle(_w("TV-channel"));

        $helper = new smHelper();
        $subuser_data = $helper->getSubuserByLogin($auth_res['login']);
        $sub_user = new smSubuser($subuser_data['id']);
        $sid = $sub_user->getId();

        if (!isset($sid)) {
            $this->getResponse()->setTitle(_w("Error — Smotron"));
            $this->setThemeTemplate('error.html');
            $this->view->assign('error_title', _w("Error getting the list of channels"));
            $this->view->assign('error', _w("Can't initialize the user"));
            $this->view->assign('error_no_disclaimer', 1);
            return;
        }

//channels
        $data['channels'] = $sub_user->getChannels();
//token
        $data['token'] = $sub_user->getToken();

        $target = waRequest::param('target_channel');
        waLog::dump($target);
        if(isset($target)){
            foreach ($data['channels'] as $key => $channel)
            {
                if($channel['id'] == $target){ $data['target'] = $key;}
            }
        }

        $this->view->assign('data', $data);
        $this->view->assign('subuser', $sub_user->get());
    }
}