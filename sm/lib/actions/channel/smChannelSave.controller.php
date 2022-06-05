<?php
class smChannelSaveController extends waJsonController
{
    public function execute()
    {
        $data = waRequest::post('channel', null);
        $image = waRequest::file('image');

        if(!is_array($data)) {$this->response = array('result' => 0, 'message' => 'Системная ошибка #NOARR'); return;}
        if(!isset($data['disabled'])){ $data['disabled'] = 1; }
        $id = $data['id'];
        $channel = new smChannel($id);
        $channel->setAll($data);
        is_null($image) ? $channel->save() : $channel->save($image);


        $this->response = array('result' => 1, 'message' => 'Данные сохранены', 'id' => $channel->getId());
    }
}
