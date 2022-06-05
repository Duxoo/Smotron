<?php

class smTariffSaveController extends waJsonController
{
    public function execute()
    {
        $data = waRequest::post('tariff');
        if(!is_array($data)) {$this->response = array('result' => 0, 'message' => 'Системная ошибка #NOARR'); return;}

        if(!isset($data['disabled'])){ $data['disabled'] = 1; }

        $new = 1;
        $id = ifempty($data['id'], 0);

        $tariff = new smTariff($id);

        if($tariff->getId()) {$new = 0;}

        $tariff->setAll($data);
        $tariff->save();

        $this->response = array('result' => 1, 'message' => 'Данные сохранены', 'id' => $tariff->getId(),'new' => $new);
    }
}