<?php

class smFrontendMyCustomChannelsListSaveController extends waJsonController
{
    public function execute()
    {
        if(!wa()->getUser()->isAuth()) {$this->response = array('result' => 0, 'message' => _w("You must log in")); return;}
        $contact_id =  wa()->getUser()->getId();
        $videos_id = waRequest::post('videos');
        $channel_name = waRequest::post('channel_name');
        $channel_id = waRequest::post('channel_id');

        $user = new smUser(wa()->getUser()->getId());
        $user_data = $user->getData();
        $tariff = new smTariff($user_data['tariff_id']);
        $tariff_data = $tariff->getData();

        if(empty($videos_id)){ $this->response = array('result' => 0, 'message' => _w("Add at least one video to the feed.")); return; }

        $video_model = new smVideoModel();
        $videos = $video_model->getVideosById($videos_id);

        $api =  new smFlussonicApi();

        $channel_model = new smChannelModel();
        // создание нового канала
        if(!isset($channel_id))
        {
            if(count($user->getUserCustomChannels()) >= $tariff_data['channel_custom_count']) { $this->response = array('result' => 0, 'message' => _w("The channel limit for your tariff has been reached!")); return; }
            $channel_exists = $channel_model->getByField(array('contact_id' => $contact_id, 'name' => $channel_name));
            if (!empty($channel_exists)) {
                $this->response = array('result' => 0, 'message' => _w("A channel with this name already exists."));
                return;
            }
            $result = $channel_model->createCustomChannel($channel_name, $contact_id);
            if($result['result'] == 0){ $this->response = $result; }
            else
            {
                $custom_channel_videos_model = new smCustomChannelVideosModel();
                foreach ($videos_id as $pos => $video)
                {
                    $custom_channel_videos_model->insert(array('channel_id' => $result['row_id'], 'position' => $pos, 'video_id' => $video));
                }
            }

            $api->concatenateVideos($videos, hash("md5",$channel_name.$result['time']));
            $this->response = $result;
        }
        // редактирование существующего
        else
        {
            // проверка на то, что мы редактируем канал, НО НЕ трогаем его имя
            $channel_exists = $channel_model->getByField(array('contact_id' => $contact_id, 'id' => $channel_id, 'name' => $channel_name));
            // проверка на то, есть ли у этого пользователя канал с таким именем
            $channel_name_exists = $channel_model->getByField(array('contact_id' => $contact_id, 'name' => $channel_name));

            // если канал у этого пользователя с таким именем есть И имя канала изменилось
            if ($channel_exists == NULL && !empty($channel_name_exists)) {
                $this->response = array('result' => 0, 'message' => _w("There is already a channel with this name."));
                return;
            }

            $post_protection = $channel_name_exists = $channel_model->getByField(array('contact_id' => $contact_id, 'id' => $channel_id));
            // проверка на отсуствие канала
            if ($post_protection == NULL) {
                $this->response = array('result' => 0, 'message' => _w("The user does not have a channel with the specified name and ID."));return;
            }

            $contact_id =  wa()->getUser()->getId();
            $channel_data = $channel_model->getById($channel_id);
            if(!isset($channel_data)) {$this->response = array('result' => 0, 'message' => _w("System error: channel not found")); return;}
            $result = $channel_model->deleteChannelById($channel_data['id'],$contact_id);

            if($result['result'] != 1){ $this->response = $result['message'];return;}
            $api->deleteChannel($channel_data['fl_channel_name']);
            $api->deleteVideo($channel_data['fl_channel_name'].'.mp4');

            $result = $channel_model->createCustomChannel($channel_name, $contact_id);
            waLog::dump($result);

            if($result['result'] == 0){ $this->response = $result; }
            else
            {
                $custom_channel_videos_model = new smCustomChannelVideosModel();
                foreach ($videos_id as $pos => $video)
                {
                    $custom_channel_videos_model->insert(array('channel_id' => $result['row_id'], 'position' => $pos, 'video_id' => $video));
                }
            }
            $api->concatenateVideos($videos, hash("md5",$channel_name.$result['time']));
            $this->response = $result;
        }
    }
}