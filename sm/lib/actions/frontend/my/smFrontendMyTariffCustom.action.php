<?php

class smFrontendMyTariffCustomAction extends waViewAction
{
    public function execute()
    {
        if(!wa()->getUser()->isAuth())
        {
            $this->setThemeTemplate('blank.html');
            wa()->getResponse()->redirect(wa()->getRouteUrl('sm/frontend').'?ar=1', 302);
            return;
        }

        $this->setThemeTemplate('my.tariff_custom.html');
        $this->setLayout(new smFrontendLayout());
        $this->getResponse()->setTitle(_w("Create tariff"));

        $user = new smUser(wa()->getUser()->getId());
        if(!$user->isUser())
        {
            wa()->getResponse()->setTitle(_w("sign in"));
            wa()->getResponse()->setStatus(404);
            $this->setThemeTemplate('error.html');
            $this->view->assign('error_title', _w("Log in or register"));
            $this->view->assign('error', _w("To make purchases, you need to log in or register"));
            $this->view->assign('error_no_disclaimer', 1);
            $this->view->assign('error_button', array('url' => '#', 'text' => _w("register"), 'class' => ''));
            return;
        }

        $settings_model = new smSettingsModel();
        $settings = array(
            'ctariff_channel_price' => $settings_model->getParam('ctariff_channel_price'),
            'ctariff_stream_price' => $settings_model->getParam('ctariff_stream_price'),
            'ctariff_min_price' => $settings_model->getParam('ctariff_min_price'),
        );
        $this->view->assign('settings', $settings);
    }
}