<?php

class smFrontendStreamAction extends waViewAction
{
    public function execute()
    {
        //$_SESSION
        $auth = new smAuth();
        $auth_res = $auth->isSubAuth();
        waLog::dump($auth_res);
        if($auth_res['result'] != true)
        {
            $this->setThemeTemplate('blank.html');
            wa()->getResponse()->redirect(wa()->getRouteUrl('sm/frontend').'?ar=1', 302);
            return;
        }

        $this->setThemeTemplate('stream.html');
        $this->setLayout(new smFrontendLayout());
        $this->getResponse()->setTitle(_w("Stream"));

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
        if(empty($data['channels'])){
            $this->getResponse()->setTitle(_w("Error — Smotron"));
            $this->setThemeTemplate('error.html');
            $this->view->assign('error_title', _w("Error getting the list of channels"));
            $this->view->assign('error', _w("Do not select any channel"));
            $this->view->assign('error_no_disclaimer', 1);
            return;
        }
//token
        $data['token'] = $sub_user->getToken();

        /*$target = waRequest::get('target_channel');*/
        if(isset($subuser_data['target_channel'])){
            foreach ($data['channels'] as $key => $channel)
            {
                if($channel['id'] == $subuser_data['target_channel'])
                {
                    $this->view->assign('target', $channel);
                }
            }
        }

        $this->view->assign('data', $data);
        $this->view->assign('subuser', $sub_user->get());
    }
}