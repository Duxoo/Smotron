<?php

class smClientsPremoderationToggleController extends waJsonController
{
    public function execute()
    {
        $channel_id = waRequest::get('id');

        $channel = new smChannel($channel_id);
        $channel_data = $channel->get();

        if($channel_data['premoderation_flag'] == 'off')
        {
            $channel->set('premoderation_flag', 'on');
            $channel->save();
            $this->response = array('result' => 1, 'message' => 'Канал стал доступен пользователю');
        }
        else
        {
            $channel->set('premoderation_flag', 'off');
            $channel->save();
            $this->response = array('result' => 1, 'message' => 'Канал больше не доступен пользователю');
        }

    }
}