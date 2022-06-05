<?php

class smFrontendMyAction extends waMyProfileAction
{
    public function execute()
    {
        parent::execute();
        $this->setThemeTemplate('my.profile.html');
        if (!waRequest::isXMLHttpRequest()) {
            $this->setLayout(new smFrontendLayout());
            $entity = new smEntity($this->contact->getId());

            $entity_info = $entity->getInfo();
            $this->view->assign('entity_info', $entity_info);
            
            $this->getResponse()->setTitle(_w('My account').' — '._w('My profile'));
        }
    }
}