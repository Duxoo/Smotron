<?php

class smClientsEditAction extends waViewAction
{
    public function execute()
    {
        $this->setLayout(new smBackendLayout());
        $id = waRequest::get('id', 0, 'int');
        $client = new smUser($id);
        $user = new waContact($id);
        $fields = array(
            "name" => $name = $user->get('name'),
            "firstname" => $name = $user->get('firstname'),
            "lastname" => $name = $user->get('lastname'),
            "phone" => $name = $user->get('phone'),
            "email" => $name = $user->get('email'),
            "company" => $name = $user->get('company'),
            "ogrn" => $name = $user->get('ogrn'),
            "inn" => $name = $user->get('inn'),
            "kpp" => $name = $user->get('kpp'),
        );
        
        $this->view->assign('jfields', smBillHelper::getFilledBillFields());
        $this->view->assign('sm_data', $client->getData());
        $this->view->assign('data', $fields);
        $this->view->assign('id', $client->getId());
    }
}