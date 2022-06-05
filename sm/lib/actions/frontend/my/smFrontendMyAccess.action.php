<?php

class smFrontendMyAccessAction extends waViewAction
{
    public function execute()
    {
        if(!wa()->getUser()->isAuth())
        {
            $this->setThemeTemplate('blank.html');
            wa()->getResponse()->redirect(wa()->getRouteUrl('sm/frontend').'?ar=1', 302);
            return;
        }

        $this->setThemeTemplate('my.access.html');
        $this->setLayout(new smFrontendLayout());
        $this->getResponse()->setTitle(_w("Access control"));

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
        $tariffs = smTariff::getUserTariffs($user_data['id']);
        $tariff_id = $user_data['tariff_id'];
        $tariff = null;
        if(isset($tariffs[$tariff_id])) {$tariff = $tariffs[$tariff_id];}

        if(!$user_data['subscribtion_valid'] && $tariff == null)
        {
            wa()->getResponse()->setTitle(_w("Error — Smotron"));
            wa()->getResponse()->setStatus(404);
            $this->setThemeTemplate('error.html');
            $this->view->assign('error_title', _w("Access control error"));
            $this->view->assign('error', _w("To start viewing it you need to activate the tariff"));
            $this->view->assign('error_no_disclaimer', 1);
            $this->view->assign('error_button', array('url' => '/my/tariff/', 'text' => _w("To subscription management"), 'class' => ''));
            return;
        }

        if(!$user_data['subscribtion_valid'] && $tariff !== null)
        {
            wa()->getResponse()->setTitle(_w("Error — Smotron"));
            wa()->getResponse()->setStatus(404);
            $this->setThemeTemplate('error.html');
            $this->view->assign('error_title', _w("Access control error"));
            $this->view->assign('error', _w("To start viewing you need to pay the fare"));
            $this->view->assign('error_no_disclaimer', 1);
            $this->view->assign('error_button', array('url' => '/my/tariff/', 'text' => _w("To subscription management"), 'class' => ''));
            return;
        }

        /*$referral = new smReferral(wa()->getUser()->getId(),'r35ahzm');
        $referral->createNode();
        waLog::dump($referral->getData());*/
        /*
         if(wa()->getUser()->isAuth()){
            $helper = new smHelper();
            $this->setThemeTemplate('my.access.html');
            $user_id = $this->getUserId();
            $error['tariff'] = false;
            $con_tariff = $helper->getContactTariff($user_id);
            if(!isset($con_tariff)){ $error['tariff'] = _w("The tariff is not paid"); }

            if (!waRequest::isXMLHttpRequest()) {
                if($error['tariff']){ $this->view->assign('tariff_flag', $error['tariff']); }
                $this->setLayout(new smFrontendLayout());
                $this->getResponse()->setTitle('Access');
            }
        }
        else
        {
            $this->setThemeTemplate('blank.html');
            $login_page = wa()->getRouteUrl('sm/loginPage');
            $this->getResponse()->redirect($login_page);
        }
         * */
    }

}