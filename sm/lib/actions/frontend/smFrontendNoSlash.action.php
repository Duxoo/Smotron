<?php

class smFrontendNoSlashAction extends waViewAction
{
    public function execute()
    {
		wa()->getResponse()->setTitle(_w("Redirection.."));
		$this->setThemeTemplate('blank.html');
		
		$uri_data = explode('?', $_SERVER['REQUEST_URI']);
		$uri = $uri_data[0].'/';
		if(isset($uri_data[1])) {$uri .= '?'.$uri_data[1];}
		wa()->getResponse()->redirect($uri, 301);
	}
}
