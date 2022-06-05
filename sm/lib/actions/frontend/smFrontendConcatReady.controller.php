<?php

class smFrontendConcatReadyController extends waJsonController {

    public function execute() {
        $flussonicApi = new smFlussonicApi();
        $contact_id = waRequest::post('contact_id');
        $video_name = waRequest::post('video_name');
        $flussonicApi->updateConfig(array('name' => $video_name, 'url' => 'file://vod/'.$video_name.".mp4"));

        $channel_model = new smChannelModel();
        $channel_model->updateChannelStatus($video_name, $contact_id);
    }
}