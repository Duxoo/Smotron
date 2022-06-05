<?php

class smFrontendMyReferralAction extends waViewAction
{
    public function execute()
    {
        if(!wa()->getUser()->isAuth())
        {
            $this->setThemeTemplate('blank.html');
            wa()->getResponse()->redirect(wa()->getRouteUrl('sm/frontend').'?ar=1', 302);
            return;
        }

        $this->setThemeTemplate('my.referral.html');
        $this->setLayout(new smFrontendLayout());
        $this->getResponse()->setTitle(_w("Referral program"));

        $user = new smUser(wa()->getUser()->getId());
        if(!$user->isUser())
        {
            $this->getResponse()->setTitle(_w("Error — Smotron"));
            $this->setThemeTemplate('error.html');
            $this->view->assign('error_title', _w("Referral program error"));
            $this->view->assign('error', _w("Can't initialize the user"));
            $this->view->assign('error_no_disclaimer', 1);
            return;
        }

        $referral = new smReferral($user->getId());
        $tree = $referral->getTree();

        $this->view->assign('tree',$tree);
        $user_tree['childs'] = $tree;
        $this->view->assign('user_tree',$user_tree);
    }
}