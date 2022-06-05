<?php

class smFrontendSaveEntityController extends waJsonController
{
    public function execute()
    {
        $data = waRequest::post('data');
        $data['contact_id'] = $this->getUserId();
        try
        {
            $entity = new smEntity($data['contact_id']);
            $entity->setInfo($data);
            $this->response = array('result' => 1);
            return;
        }
        catch(waException $e)
        {
            $this->response = array('result' => 0, 'message' => $e->getMessage());
            return;
        }

    }
}