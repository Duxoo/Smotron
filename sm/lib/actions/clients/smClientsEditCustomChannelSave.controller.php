<?php

class smClientsEditCustomChannelSaveController extends waJsonController
{
    public function execute()
    {
        $contact_id =  waRequest::post('user_id');
        $videos_id = waRequest::post('videos');
        $channel_name = waRequest::post('channel_name');
        $channel_id = waRequest::post('channel_id');

        $video_model = new smVideoModel();
        $videos = $video_model->getVideosById($videos_id);

        $api =  new smFlussonicApi();

        $channel_model = new smChannelModel();

        $lenght = strlen($contact_id) + strlen($channel_id);
        $channel_name = substr($channel_name,0, -$lenght);

        $contact_id =  wa()->getUser()->getId();
        $channel_data = $channel_model->getById($channel_id);
        if(!isset($channel_data)) {$this->response = array('result' => 0, 'message' => 'Системная ошибка: канал не найден'); return;}
        $result = $channel_model->deleteChannelById($channel_data['id'],$contact_id);

        if($result['result'] != 1){ $this->response = $result['message'];return;}
        $api->deleteChannel($channel_data['fl_channel_name']);
        $api->deleteVideo($channel_data['fl_channel_name'].'.mp4');

        $result = $channel_model->createCustomChannel($channel_name, $contact_id);

        if($result['result'] == 0){ $this->response = $result; }
        else
        {
            $custom_channel_videos_model = new smCustomChannelVideosModel();
            $data = array();
            foreach ($videos_id as $pos => $video)
            {
                //$data, array('channel_id' => $result['row_id'], 'position' => $pos, 'video_id' => $video));
                $custom_channel_videos_model->insert(array('channel_id' => $result['row_id'], 'position' => $pos, 'video_id' => $video));
            }
        }
        $api->concatenateVideos($videos,hash("md5",$channel_name.$contact_id.$result['row_id']));
        $this->response = $result;

    }
}