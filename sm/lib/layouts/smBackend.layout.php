<?php

class smBackendLayout extends waLayout
{
    public function execute()
    {
		$this->view->assign(array(
            'page' => $this->getPage(),
        ));
    }
	
	protected function getPage()
    {
        $default_page = 'clients';
		$module = waRequest::get('module', 'backend');
        $page = waRequest::get('action', ($module == 'backend') ? $default_page : 'dashboard');

		if ($module != 'backend') {$page = $module.':'.$page;}
        if($module == 'dashboard') {$page = 'dashboard';}
        if($module == 'clients') {$page = 'clients';}
        if($module == 'settings') {$page = 'settings';}
		return $page;
    }
}

