<?php

class smTariffDeleteController extends waJsonController
{
    public function execute()
    {
        $id = waRequest::post('id', 0, 'int');
        $channel_model = new smTariffModel();
        $channel_model->updateById($id, array('hidden' => 1));
        $this->response = array('result' => 1, 'message' => 'Данные удалены');
    }
}