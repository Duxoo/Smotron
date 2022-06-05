<?php

class smFrontendMyBalanceAction extends waViewAction
{
    public function execute()
    {
        if(!wa()->getUser()->isAuth())
		{
			$this->setThemeTemplate('blank.html');
			wa()->getResponse()->redirect(wa()->getRouteUrl('sm/frontend').'?ar=1', 302);
			return;
		}
		
        $this->setThemeTemplate('my.balance.html');
		$this->setLayout(new smFrontendLayout());
		$this->getResponse()->setTitle(_w("Balance - Smotron"));
		
		$user = new smUser();
		if(!$user->isUser()) {$this->view->assign('error', _w("User is not found")); return;}
		$this->view->assign('user_data', $user->getData());
    }
}