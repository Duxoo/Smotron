<?php

class smBackendController extends waViewController
{
    public function execute()
    {
       $this->executeAction(new smClientsAction());
    }
}