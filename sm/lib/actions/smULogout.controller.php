<?php

class smUlogoutController extends waJsonController
{
    public function execute()
    {
        $uuid = waRequest::cookie('uuid');
        $auth = new smAuth();
        $auth->clearSession($uuid);
        setcookie('uuid', "",time()-36000,'/');
        setcookie('login', "",time()-36000,'/');
        $this->redirect('/');
    }
}