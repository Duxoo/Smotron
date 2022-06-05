<?php

class smFrontendMyAccessSubuserAction extends waViewAction
{
    public function execute()
    {
        if(!wa()->getUser()->isAuth())
        {
            $this->setThemeTemplate('blank.html');
            wa()->getResponse()->redirect(wa()->getRouteUrl('sm/frontend').'?ar=1', 302);
            return;
        }

        $this->setThemeTemplate('my.access.subuser.html');
        $this->setLayout(new smFrontendLayout());
        $this->getResponse()->setTitle(_w("User management"));

        $user = new smUser(wa()->getUser()->getId());
        if(!$user->isUser())
        {
            $this->getResponse()->setTitle(_w("Error — Smotron"));
            $this->setThemeTemplate('error.html');
            $this->view->assign('error_title', _w("Access control error"));
            $this->view->assign('error', _w("Can't initialize the user"));
            $this->view->assign('error_no_disclaimer', 1);
            return;
        }

        $user_data = $user->getData();
        $tariffs = smTariff::getUserTariffs($user_data['id']);
        $tariff_id = $user_data['tariff_id'];
        $tariff = null;
        if(isset($tariffs[$tariff_id])) {$tariff = $tariffs[$tariff_id];}

        $sub_id = waRequest::param('sub_user_id', 0, 'int');
        $subuser = new smSubuser($sub_id);
        $tariff = new smTariff($tariff_id);
        $channels = $tariff->getChannels();
        $user_data['channels'] = $subuser->getChannels();
        $user_data['sub_user'] = $subuser->get();

        if ($user_data['sub_user']['parent_contact_id'] != $user_data['id']) {
            $this->getResponse()->setTitle(_w("Error — Smotron"));
            $this->setThemeTemplate('error.html');
            $this->view->assign('error_title', _w("Access control error"));
            $this->view->assign('error', _w("You do not have access to edit this user."));
            $this->view->assign('error_no_disclaimer', 1);
            wa()->getResponse()->setStatus(403);
            return;
        }

        if(!$user_data['subscribtion_valid'] && $tariff == null)
        {
            $this->getResponse()->setTitle(_w("Error — Smotron"));
            $this->setThemeTemplate('error.html');
            $this->view->assign('error_title', _w("Access control error"));
            $this->view->assign('error', _w("The tariff is not enabled"));
            $this->view->assign('error_no_disclaimer', 1);
            return;
        }

        if(!$user_data['subscribtion_valid'] && $tariff !== null)
        {
            $this->getResponse()->setTitle(_w("Error — Smotron"));
            $this->setThemeTemplate('error.html');
            $this->view->assign('error_title', _w("Access control error"));
            $this->view->assign('error', _w("The tariff is not paid"));
            $this->view->assign('error_no_disclaimer', 1);
            return;
        }

        foreach($channels as $key => $channel)
        {
            if(is_array($user_data['channels'])){
                foreach($user_data['channels'] as $u_key => $user_channel)
                {
                    if($channel['id'] == $user_channel['id'])
                    {
                        $channels[$key]['checked'] = true;
                    }
                }
            }
        }


        $channels_model = new smChannelModel();
        $custom_channels = $channels_model->getByField(array(
            'is_custom' => 1,
            'disabled' => 0,
            'contact_id' => wa()->getUser()->getId(),
        ),true);

        foreach ($custom_channels as $custom_channel)
        {
            if(is_array($user_data['channels'])) {
                foreach($user_data['channels'] as $u_key => $user_channel)
                {
                    if($custom_channel['id'] == $user_channel['id'])
                    {
                        $custom_channel['checked'] = true;
                    }
                }
            }
            if($user_data['premoderation'] == 'on')
            {
                if($custom_channel['premoderation_flag'] == 'on')
                {
                    $channels[] = $custom_channel;
                }
            }
            else
            {
                $channels[] = $custom_channel;
            }
        }

        $contactSubuserModel = new smSubuserModel();
        $sub_user_info = $contactSubuserModel->getById($sub_id);
        foreach ($channels as $key => $value) {
            if ($sub_user_info['target_channel'] == $value['id']) {
                $channels[$key]['target_channel'] = true;
            }
        }

        $this->view->assign('channels', $channels);
        $this->view->assign('contact_id', $subuser->getId());
        $this->view->assign('data', $user_data);
        $this->view->assign('id', $subuser->getId());

        /*if(wa()->getUser()->isAuth())
        {
            $this->setThemeTemplate('my.access.subuser.html');
            $this->setLayout(new smFrontendLayout());

            $user = new smUser($this->getUserId());

            $sub_id = waRequest::param('sub_user_id', 0, 'int');
            $subuser = new smSubuser($sub_id);

            $tariff = new smTariff($user->getData('tariff_id'));
            $channels = $tariff->getChannels();

            $user_data = $subuser->get();
            foreach($channels as $key => $channel)
            {
                if(is_array($user_data['channels'])){
                    foreach($user_data['channels'] as $u_key => $user_channel)
                    {
                        if($channel['id'] == $user_channel['channel_id'])
                        {
                            $channels[$key]['checked'] = true;
                        }
                    }
                }
            }

            $this->view->assign('channels', $channels);
            $this->view->assign('contact_id', $subuser->getId());
            $this->view->assign('data', $user_data);
            $this->view->assign('id', $subuser->getId());
        }
        else
        {
            $this->setThemeTemplate('blank.html');
            $login_page = wa()->getRouteUrl('sm/loginPage');
            $this->getResponse()->redirect($login_page);
        }*/
    }
}