<?php

class smFrontendMyStreamAction extends waViewAction
{
    public function execute()
    {
        if(!wa()->getUser()->isAuth())
        {
            $this->setThemeTemplate('blank.html');
            wa()->getResponse()->redirect(wa()->getRouteUrl('sm/frontend').'?ar=1', 302);
            return;
        }

        $this->setThemeTemplate('my.stream.html');
        $this->setLayout(new smFrontendLayout());
        $this->getResponse()->setTitle(_w("Viewing a stream"));

        $user = new smUser(wa()->getUser()->getId());
        if(!$user->isUser())
        {
            wa()->getResponse()->setTitle(_w("sign in"));
            wa()->getResponse()->setStatus(403);
            $this->setThemeTemplate('error.html');
            $this->view->assign('error_title', _w("Log in or register"));
            $this->view->assign('error', _w("To make purchases, you need to log in or register"));
            $this->view->assign('error_no_disclaimer', 1);
            $this->view->assign('error_button', array('url' => '/?ar=1', 'text' => _w("register"), 'class' => ''));
            return;
        }

        $user_data = $user->getData();
        if(!$user_data['subscribtion_valid'])
        {
            wa()->getResponse()->setTitle(_w("Connect the tariff"));
            wa()->getResponse()->setStatus(403);
            $this->setThemeTemplate('error.html');
            $this->view->assign('error_title', _w("Buy or renew your subscription"));
            $this->view->assign('error', _w("To start viewing you need to buy or renew your subscription"));
            $this->view->assign('error_no_disclaimer', 1);
            $this->view->assign('error_button', array('url' => '/my/tariff/', 'text' => _w("To subscription management"), 'class' => ''));
            return;
        }
        else
        {
            $tariff = new smTariff($user_data['tariff_id']);
            $tariff_data = $tariff->getData();
            $model = new smTariffChannelsModel();
            $data['channels'] = $model->getChannelsDataByTariff($user_data['tariff_id']);
            $data['token'] = $user->getToken();
            $channels_model = new smChannelModel();
            $custom_channels = $channels_model->getByField(array(
                'is_custom' => 1,
                'disabled' => 0,
                'contact_id' => wa()->getUser()->getId(),
            ),true);

            $custom_channels = array_slice($custom_channels, 0, $tariff_data['channel_custom_count']);

            foreach ($custom_channels as $custom_channel)
            {
                if($user_data['premoderation'] == 'on')
                {
                    if($custom_channel['premoderation_flag'] == 'on')
                    {
                        $data['channels'][] = $custom_channel;
                    }
                }
                else
                {
                    $data['channels'][] = $custom_channel;
                }
            }
            if (count($data['channels']) == 0) {
                wa()->getResponse()->setTitle(_w("You don't have channels"));
                wa()->getResponse()->setStatus(403);
                $this->setThemeTemplate('error.html');
                $this->view->assign('error_title', _w("There are no channels to view in your tariff"));
                $this->view->assign('error', _w("To start viewing you must have a subscription with the ability to view channels"));
                $this->view->assign('error_no_disclaimer', 1);
                $this->view->assign('error_button', array('url' => '/my/tariff/', 'text' => _w("To subscription management"), 'class' => ''));
                return;
            }
            $target = waRequest::get('target_channel');
            if(isset($target)){
                foreach ($data['channels'] as $key => $channel)
                {
                    if($channel['id'] == $target){ $data['target'] = $key;}
                }
            }
            $this->view->assign('data', $data);
        }
    }
}