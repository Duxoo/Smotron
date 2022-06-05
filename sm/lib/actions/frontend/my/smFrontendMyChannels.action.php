<?php

class smFrontendMyChannelsAction extends waViewAction
{
    public function execute()
    {
        if(!wa()->getUser()->isAuth())
        {
            $this->setThemeTemplate('blank.html');
            wa()->getResponse()->redirect(wa()->getRouteUrl('sm/frontend').'?ar=1', 302);
            return;
        }

        $this->setThemeTemplate('my.channels.html');
        $this->setLayout(new smFrontendLayout());
        $this->getResponse()->setTitle(_w("TV-channel"));

        $user = new smUser(wa()->getUser()->getId());
        if(!$user->isUser())
        {
            wa()->getResponse()->setTitle(_w("sign in"));
            wa()->getResponse()->setStatus(404);
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
            wa()->getResponse()->setStatus(404);
            $this->setThemeTemplate('error.html');
            $this->view->assign('error_title', _w("Buy or renew your subscription"));
            $this->view->assign('error', _w("To start viewing you need to buy or renew your subscription"));
            $this->view->assign('error_no_disclaimer', 1);
            $this->view->assign('error_button', array('url' => '/my/tariff/', 'text' => _w("To subscription management"), 'class' => ''));
            return;
        }
        else
        {
            $model = new smTariffChannelsModel();
            $data['channels'] = $model->getChannelsDataByTariff($user_data['tariff_id']);
            $data['token'] = $user->getToken();
            $this->view->assign('data', $data);
        }
    }
}