<?php

class smTariffMoveController extends waJsonController
{
    public function execute()
    {
        waLog::dump(waRequest::post('data'));
        $data = waRequest::post('data');
        $model = new smTariffModel();
        foreach ($data as $sort => $id)
        {
            $model->updateById($id,array('sort' => $sort));
        }
    }
}