<?php

class smFrontendMyVideoUploadController extends waJsonController
{
    public function execute()
    {
        if(!wa()->getUser()->isAuth()) {$this->response = array('result' => 0, 'message' => _w("You must log in")); return;}
        $videos = $_FILES['video'];

        waLog::dump('start', 'time.log');

        $videos = $this->reArrayFiles($videos);

        foreach ($videos as $video)
        {
            if(!is_array($video)) {$this->response = array('result' => 0, 'message' => _w("System error #NOARR")); return;}

            if(!isset($_FILES) && $_FILES['video']['error'] != 0){ $this->response = array('result' => 0, 'message' => _w("File upload error: FILES_ERROR")); return; }
            if(empty($_FILES['video']['name'])){ $this->response = array('result' => 0, 'message' => _w("File upload error: invalid name")); return; }
            if(!preg_match('/video\/*/', $video['type'])) {
                $this->response = array('result' => 0, 'message' => _w("File upload error: Only video format files are available for download"));
                return;
            }

            $contact_id = wa()->getUser()->getId();
            $contact_model = new waContactModel();
            $contact_data = $contact_model->getById($contact_id);
            if(!$contact_data) {$this->response = array('result' => 0, 'message' => _w("Contact not found")); return;}

            $path = smVideoHelper::getDir();
            if(file_exists($path.'/'.$contact_id.'/'.$video['name']) && $video['name']) {$this->response = array('result' => 0, 'message' => _w("A file with this name already exists!")); return;}
            if(!is_dir($path.'/'.$contact_id)) {
                mkdir($path.'/'.$contact_id, 0777, true);
            }
            move_uploaded_file($video['tmp_name'], $path.'/'.$contact_id.'/'.basename($video['name']));

            $full_path = $path.'/'.$contact_id.'/'.basename($video['name']);
            $path_no_file_name = $path.'/'.$contact_id.'/';
            $info = pathinfo($full_path);
            $progress_file = $path_no_file_name.$info['filename'].".txt";
            $final_name = $path_no_file_name.$info['filename'].".mp4";

            exec('ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 '.$path.'/'.$contact_id.'/'.basename($video['name']).' 2>&1', $video['duration']);

            unset($video['tmp_name']);
            unset($video['error']);
            $video['uploadtime'] = date('Y-m-d H:i:s');
            $video['contact_id'] = $contact_id;
            $video['status'] = 'В обработке';
            $video['duration'] = date("H:i:s", mktime(0, 0, round($video['duration'][0]))); //todo ffmpeg duration
            $model = new smVideoModel();
            $model->insert($video);
            $row = $model->getByField(array(
                'contact_id' => $contact_id,
                'name' => $video['name'],
            ));

            $flussonicApi = new smFlussonicApi();
            $api_url = str_replace('https://', '', $flussonicApi->api_flussonic_url).'upload';
            $controller = wa()->getRouteUrl('sm/frontend/transcodingReady');
            $controller = substr($controller, 1);
            $url = wa()->getRootUrl(true).$controller;

            $file_row = "{$full_path} {$final_name} {$progress_file} {$info['filename']} {$contact_id} {$flussonicApi->login} {$flussonicApi->password} {$api_url} {$row['id']} {$url}\n" ;
            file_put_contents($_SERVER['DOCUMENT_ROOT']."/videos.txt", $file_row ,FILE_APPEND);
            //$flussonicApi->transcoding($full_path, $final_name, $progress_file, $info['filename'], $contact_id, $row['id']);
            $this->response = array('result' => 1, 'message' => 'Файл успешно загружен');
        }

        if(!file_exists($_SERVER['DOCUMENT_ROOT']."/run.txt")){ $flussonicApi->transcodingqueue(); }
        return;
    }


    public function reArrayFiles($file_post) {

        $file_ary = array();
        $file_count = count($file_post['name']);
        $file_keys = array_keys($file_post);

        for ($i=0; $i<$file_count; $i++) {
            foreach ($file_keys as $key) {
                $file_ary[$i][$key] = $file_post[$key][$i];
            }
        }

        return $file_ary;
    }
}