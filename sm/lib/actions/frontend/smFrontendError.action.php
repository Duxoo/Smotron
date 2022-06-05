<?php

class smFrontendErrorAction extends waViewAction
{
    public function execute()
    {
		wa()->getResponse()->setStatus(404);
		wa()->getResponse()->setTitle(_w("Page not found - Smotron"));
		$this->setLayout(new smFrontendLayout());
		$this->setThemeTemplate('error.html');
	}
}
