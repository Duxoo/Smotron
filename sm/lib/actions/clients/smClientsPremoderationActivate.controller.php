<?php

class smClientsPremoderationActivateController extends waJsonController
{
    public function execute()
    {
        $premoderation = waRequest::post('premoderation', null);
        if(!isset($premoderation)) {$this->response = array('result' => 0, 'message' => 'Системная ошибка #NOVAR'); return;}
        $user_id = waRequest::post('id', null);
        waLog::dump($user_id,'premod.log');
        $user = new smUser($user_id);
        $user->set('premoderation', $premoderation);
        $user->save();
    }
}