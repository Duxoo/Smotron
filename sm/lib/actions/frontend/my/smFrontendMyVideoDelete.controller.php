<?php

class smFrontendMyVideoDeleteController extends waJsonController
{
    public function execute()
    {
        if(!wa()->getUser()->isAuth()) {$this->response = array('result' => 0, 'message' => _w("You must log in")); return;}
        $contact_id =  wa()->getUser()->getId();
        $id = waRequest::get('id');
        if(!isset($id)) {$this->response = array('result' => 0, 'message' => _w("System error #NOPARAMETR")); return;}
        $model = new smVideoModel();
        $row = $model->getById($id);
        $path = smVideoHelper::getDir();
        $filename = $path.'/'.$contact_id.'/'.basename($row['name']);
        $info = pathinfo($filename);
        unlink($filename);

        $filename = $path.'/'.$contact_id.'/'.basename($info['filename'].'.mp4');
        if(file_exists($filename))
            unlink($filename);
        $model->deleteById($id);
        $this->response = array('result' => 1, 'message' => _w("Video deleted"));
    }
}