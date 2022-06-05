<?php

class smFrontendMyCustomChannelsAction extends waViewAction
{
    public function execute()
    {
        if (!wa()->getUser()->isAuth()) {
            $this->setThemeTemplate('blank.html');
            wa()->getResponse()->redirect(wa()->getRouteUrl('sm/frontend') . '?ar=1', 302);
            return;
        }

        $this->setLayout(new smFrontendLayout());

        /*
        $this->setThemeTemplate('error.html');
        $this->view->assign('error_title', 'Страница ещё не готова.');
        $this->view->assign('error', 'Приносим извинения за неудобства.');
        $this->view->assign('error_no_disclaimer', 1);*/
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
        $tariff = new smTariff($user_data['tariff_id']);
        $tariff_data = $tariff->getData();

        if(!$user_data['subscribtion_valid'])
        {
            wa()->getResponse()->setTitle(_w("Connect the tariff"));
            wa()->getResponse()->setStatus(404);
            $this->setThemeTemplate('error.html');
            $this->view->assign('error_title', _w("Buy or renew your subscription"));
            $this->view->assign('error', _w("To start using your own channels, connect a tariff that provides access to your own channels."));
            $this->view->assign('error_no_disclaimer', 1);
            $this->view->assign('error_button', array('url' => '/my/tariff/', 'text' => _w("To subscription management"), 'class' => ''));
            return;
        }

        if($tariff_data['channel_custom_count'] < 1)
        {
            wa()->getResponse()->setTitle(_w("Connect the tariff"));
            wa()->getResponse()->setStatus(404);
            $this->setThemeTemplate('error.html');
            $this->view->assign('error_title', _w("Subscription error"));
            $this->view->assign('error', _w("Your tariff does not allow you to use this service."));
            $this->view->assign('error_no_disclaimer', 1);
            $this->view->assign('error_button', array('url' => '/my/tariff/', 'text' => _w("To subscription management"), 'class' => ''));
            return;
        }

        $video_model = new smVideoModel();
        $videos = $video_model->getVideos(wa()->getUser()->getId());
        $this->view->assign('videos',$videos);
        $this->getResponse()->setTitle(_w("My channels"));
        $this->setThemeTemplate('my.custom_channels.html');
        return;
    }
}