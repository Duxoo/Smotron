<?php

class smClientsEditCustomChannelAction extends waViewAction
{
    protected function mySort($a, $b) {
        if ($a['position'] === $b['position']) return 0;
        return $a['position'] > $b['position'] ? 1 : -1;
    }

    public function execute()
    {
        $this->setLayout(new smBackendLayout());
        $channel_id = waRequest::get('channel_id');
        $contact_id = waRequest::get('user_id');

        $channel_model = new smChannelModel();
        $channel_data = $channel_model->getById($channel_id);

        $user = new smUser($contact_id);

        if(!isset($channel_id)) {$this->response = array('result' => 0, 'message' => 'Системная ошибка #NOPARAMETR'); return;}

        if(!isset($channel_data)) {$this->response = array('result' => 0, 'message' => 'Системная ошибка #NOCHANNEL'); return;}

        $custom_ch_v_model = new smCustomChannelVideosModel();
        $videos = $custom_ch_v_model->getByField(array('channel_id' => $channel_id),true);
        uasort($videos, array($this,'mySort'));

        $video_model = new smVideoModel();

        foreach ($videos as $key => $value)
        {
            $videos[$key]['data'] = $video_model->getById($value['video_id']);
        }

        $all_videos = $video_model->getVideos(wa()->getUser()->getId());

        $this->view->assign('user_id',$contact_id);
        $this->view->assign('channel_data',$channel_data);
        $this->view->assign('all_videos',$all_videos);
        $this->view->assign('videos',$videos);
        return;
    }
}