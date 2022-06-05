<?php

class smClientsPromocodeSaveController extends waJsonController
{
    public function execute()
    {
        waLog::dump(waRequest::post('promocode'));
        $data = waRequest::post('promocode');
        if(!is_array($data)) {$this->response = array('result' => 0, 'message' => 'Системная ошибка #NOARR'); return;}

        $new = 1;
        $id = ifempty($data['id'], 0);

        $promocode = new smPromocode($id);
        if($promocode->getId()) {$new = 0;}

        $data['datetime_start'] = date('Y-m-d H:i:s');
        $data['datetime_end'] = date('Y-m-d H:i:s',strtotime($data['datetime_end']));



        $promocode->setAll($data);
        $promocode->save();

        $this->response = array('result' => 1, 'message' => 'Данные сохранены', 'id' => $promocode->getId(),'new' => $new);
    }
}