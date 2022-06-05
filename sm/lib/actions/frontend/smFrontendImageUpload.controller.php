<?php

class smFrontendImageUploadController extends waJsonController
{
    public function execute()
    {
        if(!wa()->getUser()->isAuth()) {$this->response = array('result' => 0, 'message' => _w("You must log in")); return;}
		$id = wa()->getUser()->getId();
        $file = waRequest::file('photo');
        if(!$file->uploaded()) {$this->response = array('result' => 0, 'message' => _w("Error: file not loaded")); return;}

        try {$img = $file->waImage();}
		catch(Exception $e) {$this->response = array('result' => 0, 'message' => _w("Error: the file is not an image")); return;}
		
        $temp_dir  = wa()->getDataPath('photos', true, 'sm');
        $temp_url = wa()->getDataUrl('photos', true, 'sm');
        $fname = uniqid($id.'_').'.'.$img->getExt();
        $img->resize(1280, 720);
        $img->save($temp_dir.'/'.$fname, 90);
        list($w,$h,$t) = getimagesize($temp_dir.'/'.$fname);

        $this->response = array('result' => 1, 'img_path' => $fname, 'img_url' => $temp_url.'/'.$fname, 'w' => $w, 'h' => $h);
    }
}