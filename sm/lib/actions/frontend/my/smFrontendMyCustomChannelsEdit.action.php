<?php

class smFrontendMyCustomChannelsEditAction extends waViewAction
{
    protected function mySort($a, $b) {
        if ($a['position'] === $b['position']) return 0;
        return $a['position'] > $b['position'] ? 1 : -1;
    }

    public function execute()
    {
        // неавторизован
        if (!wa()->getUser()->isAuth()) {
            $this->setThemeTemplate('blank.html');
            wa()->getResponse()->redirect(wa()->getRouteUrl('sm/frontend') . '?ar=1', 302);
            return;
        }

        $this->setThemeTemplate('my.custom_channels_edit.html');
        $this->setLayout(new smFrontendLayout());

        $contact_id =  wa()->getUser()->getId();
        $channel_id = waRequest::get('id');


        $channel_model = new smChannelModel();
        $channel_data = $channel_model->getById($channel_id);

        // нет канала
        if (empty($channel_data)) {
            $this->getResponse()->setTitle(_w("Error — Smotron"));
            $this->setThemeTemplate('error.html');
            wa()->getResponse()->setStatus(403);
            $this->view->assign('error_title', _w("Access control error"));
            $this->view->assign('error', _w("You do not have permission to view"));
            $this->view->assign('error_no_disclaimer', 1);
            return;
        }

        $user = new smUser($contact_id);

        // существование пользователя
        if(!$user->isUser())
        {
            $this->getResponse()->setTitle(_w("Error — Smotron"));
            $this->setThemeTemplate('error.html');
            wa()->getResponse()->setStatus(403);
            $this->view->assign('error_title', _w("Access control error"));
            $this->view->assign('error', _w("Can't initialize the user"));
            $this->view->assign('error_no_disclaimer', 1);
            return;
        }

        $user_data = $user->getData();

        // канал не принадлежит пользователю
        if ($channel_data['contact_id'] != $user_data['id']) {
            $this->getResponse()->setTitle(_w("Error — Smotron"));
            $this->setThemeTemplate('error.html');
            wa()->getResponse()->setStatus(403);
            $this->view->assign('error_title', _w("Access control error"));
            $this->view->assign('error', _w("You don't have rights to view this channel"));
            $this->view->assign('error_no_disclaimer', 1);
            return;
        }

        $tariffs = smTariff::getUserTariffs($user_data['id']);
        $tariff_id = $user_data['tariff_id'];
        $tariff = null;

        if (isset($tariffs[$tariff_id])) {
            $tariff = $tariffs[$tariff_id];
        }

        // если нет такого тарифа или у пользователя закончилась подписка
        if(!$user_data['subscribtion_valid'] && $tariff == null)
        {
            $this->getResponse()->setTitle(_w("Error — Smotron"));
            $this->setThemeTemplate('error.html');
            wa()->getResponse()->setStatus(403);
            $this->view->assign('error_title', _w("Access control error"));
            $this->view->assign('error', _w("The tariff is not enabled"));
            $this->view->assign('error_no_disclaimer', 1);
            return;
        }

        // эм, тариф не подключен, шта? TODO спросить
        if(!$user_data['subscribtion_valid'] && $tariff !== null)
        {
            $this->getResponse()->setTitle(_w("Error — Smotron"));
            $this->setThemeTemplate('error.html');
            wa()->getResponse()->setStatus(403);
            $this->view->assign('error_title', _w("Access control error"));
            $this->view->assign('error', _w("The tariff is not paid"));
            $this->view->assign('error_no_disclaimer', 1);
            return;
        }


        if(!isset($channel_id)) {$this->response = array('result' => 0, 'message' => _w("System error #NOPARAMETR")); return;}

        if(!isset($channel_data)) {$this->response = array('result' => 0, 'message' => _w("System error #NOCHANNEL")); return;}

        $this->getResponse()->setTitle(_w('сhannel','сhannels',1).' '.$channel_data['name']);

        $custom_ch_v_model = new smCustomChannelVideosModel();
        $videos = $custom_ch_v_model->getByField(array('channel_id' => $channel_id),true);
        uasort($videos, array($this,'mySort'));

        $video_model = new smVideoModel();

        foreach ($videos as $key => $value)
        {
            $videos[$key]['data'] = $video_model->getById($value['video_id']);
        }

        $all_videos = $video_model->getVideos(wa()->getUser()->getId());

        $lenght = strlen($contact_id);
        $channel_name = substr($channel_data['name'],0, -$lenght);

        $this->view->assign('channel_data',$channel_data);
        $this->view->assign('channel_name',$channel_name);
        $this->view->assign('all_videos',$all_videos);
        $this->view->assign('videos',$videos);
        return;
    }
}