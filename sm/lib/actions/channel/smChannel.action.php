<?php

class smChannelAction extends waViewAction
{
    public function execute()
    {
        $this->setLayout(new smBackendLayout());
        $id = waRequest::get('id', 0, 'int');
        $channel = new smChannel($id);
        $this->view->assign('data', $channel->get());
        $this->view->assign('id', $channel->getId());
    }
}