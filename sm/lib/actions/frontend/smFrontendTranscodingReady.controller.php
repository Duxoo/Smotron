<?php

class smFrontendTranscodingReadyController extends waJsonController {

    public function execute() {
       //todo after transcoding and upload to flussonic server
        waLog::dump('ready', 'time.log');
        $file_name  = waRequest::post('name').'.mp4';
        $video_id = waRequest::post('video_id');
        if(!isset($video_id)){ $this->response = array('result' => 0, 'message' => _w("Data error")); return; }
        $data = array(
            'name' => $file_name,
            'type' => 'video/mp4',
            'status' => 'Готов',
        );
        $model = new smVideoModel();
        $model->updateById($video_id, $data);
        //waLog::dump(waRequest::post());
    }
}