<?php

class smFrontendMyCustomChannelsDeleteController extends waJsonController
{
    public function execute()
    {
        if(!wa()->getUser()->isAuth()) {$this->response = array('result' => 0, 'message' => _w("You must log in")); return;}
        $contact_id =  wa()->getUser()->getId();
        $channel_id = waRequest::get('id');
        if(!isset($channel_id)) {$this->response = array('result' => 0, 'message' => _w("System error #NOPARAMETR")); return;}
        $channel_model = new smChannelModel();
        $channel_data = $channel_model->getByField(array('id' => $channel_id, 'contact_id' => $contact_id));
        waLog::dump($channel_data);
        if(!isset($channel_data)) {$this->response = array('result' => 0, 'message' => _w("System error #NOCHANNEL")); return;}
        $api = new smFlussonicApi();
        $result = $channel_model->deleteChannelById($channel_data['id'],$contact_id);

        if($result['result'] != 1){ $this->response = $result['message'];return;}

        $api->deleteChannel($channel_data['fl_channel_name']);
        $api->deleteVideo($channel_data['fl_channel_name'].'.mp4');
        $this->response = $result;
        return;
    }
}