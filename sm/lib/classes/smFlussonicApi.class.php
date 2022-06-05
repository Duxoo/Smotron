<?php

class smFlussonicApi {

    public $flussonic_url;
/*    public $api_flussonic_url = "https://stream.smotron.tv/flussonic/api/";
    public $api_tv_url = "https://stream.smotron.tv/tv/api/";
    public $api_tv_epg_url = "https://stream.smotron.tv/tv/";*/
    public $api_flussonic_url;
    public $api_tv_url;
    public $api_tv_epg_url;
    public $login;
    public $password;

    public function __construct() {
        $settings_model = new smSettingsModel();
        $this->flussonic_url = $settings_model->getParam('flussonic_url');
        $this->api_flussonic_url = $this->flussonic_url.'flussonic/api/';
        $this->api_tv_url = $this->flussonic_url.'tv/api/';
        $this->api_tv_epg_url = $this->flussonic_url.'tv/';
        $this->login = $settings_model->getParam('flussonic_login');
        $this->password = $settings_model->getParam('flussonic_password');
    }

    public static function test($chanel_code, $token) {
        $settings_model = new smSettingsModel();
        $flussonic_url = $settings_model->getParam('flussonic_url');
        return "<iframe id='main_tv' style='width: 100%;height: 50vh; border: unset;'
                allowfullscreen src='{$flussonic_url}{$chanel_code}/embed.html?token={$token}'></iframe>";
    }

    public static function getStream($chanel_code, $token) {
        $settings_model = new smSettingsModel();
        $flussonic_url = $settings_model->getParam('flussonic_url');
        return "<video style='width: 100%; height: 100% border: unset;' id='example-video' muted autoplay  class='video-js vjs-default-skin' controls> <source 
                src='{$flussonic_url}{$chanel_code}/index.m3u8?token={$token}' type='application/x-mpegURL'></video>";
    }

    /**
     * @param $chanel_code
     * @param $token
     * @return string
     */
    public function getVideoSourceUrl($chanel_code, $token) {
        return "{$this->flussonic_url}{$chanel_code}/embed.html?token={$token}";
    }

    public function getOriginalVideoSourceUrl($chanel_code, $token) {
        waLog::dump("{$this->flussonic_url}$chanel_code/index.m3u8?token={$token}");
        return "{$this->flussonic_url}{$chanel_code}/index.m3u8?token={$token}";
    }

    public function getPreview($chanel_code, $token) {
        waLog::dump("{$this->flussonic_url}{$chanel_code}/preview.jpg?token={$token}");
        return "{$this->flussonic_url}{$chanel_code}/preview.jpg?token={$token}";
    }

    private function curl($url, $params = null, $is_array = true) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT,60);
        curl_setopt($ch, CURLOPT_USERPWD, $this->login.":".$this->password);
        if($params) {
            curl_setopt($ch, CURLOPT_POST, true);
            if($is_array)
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            else
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        $obj = curl_exec($ch);
        curl_close($ch);
        $obj = json_decode($obj, true);
        return $obj;
    }

    public function getMedia() {
        return $this->curl($this->api_flussonic_url."media");
    }

    public function getFlussonicConfiguration() {
        return $this->curl($this->api_flussonic_url."read_config");
    }

    //с помощью этой функции можно добавлять/изменять стримы, createUpdateStream не нужен(в теории)
    public function updateConfig($data) {
        //сборка конфигурационного файла с транскодированием
        $url = "{'streams': {'{$data['name']}': {'meta': {'iptv': 'true'},
         'urls': [{'url': '{$data['url']}'}],
         'epg':'on',
         'transcoder':{'global':{'hw':'nvenc'},'audio':{'bitrate':64000,'codec':'aac'},
         'video':{'0':{'bitrate':1000000,'codec':'h264'}}},
         'auth': {'url': 'iptv://localhost'}}}}";
        return $this->curl($this->api_flussonic_url."modify_config", $url, false);
    }

    /**
     * @param $channel string
     * @return bool|mixed|string
     */
    public function deleteChannel($channel) {
        $url = "{'streams':{'{$channel}':null}}";
        return $this->curl($this->api_flussonic_url."modify_config", $url, false);
    }

    public function addUserFolder($contact_id) {
        $url = "{'vods': {'vod': {'urls': [ {'url': '{$contact_id}'}]}}}";
        return $this->curl($this->api_flussonic_url."modify_config", $url, false);
    }

    /**
     * @return bool|mixed|string
     */
    public function deleteVideo($video) {
        //"{'vods':{'vod':{'urls':[{'url':'priv'},{'url':'abc'},{'url':'fsfdf'},{'url':'sd'}]}}}"
        $url = "";
        return $this->curl($this->api_flussonic_url."remove_file?dir=priv&prefix=vod&subpath=%2F{$video}", $url, false);
    }

    /**
     * @param $data array('name' => channel_name, 'url' => if file then value file://directory/file.mp4)
     * @return bool|mixed|string
     * пока эта функция не нужна, по большей части она не делает нужного функционала, только базу. пока не использовать
     */
    public function createUpdateStream($data) {
        //$url = "stream {meta: {iptv: 'true'}, {$data['name']}, auth: {url: 'iptv://localhost'}, {transcoder: {global: {hw: 'nvenc'}}}, { url {$data['path']};}";
        //{streams: {{$data['name']}: {meta: {iptv: 'true'}, auth: {url: 'iptv://localhost'}}}}
        $url = "stream {$data['name']} { url {$data['path']}}";
        return $this->curl($this->api_flussonic_url."config/stream_create", $url, false);
    }

    /**
     * @param $data array('name' => name, 'email' => email@mail.ru, 'max_sessions' => 5)
     * @return array(id => user_id_in_flussonic_system, 'token' => token_for_this_user)
     *
     */
    public function createUser($data) {
        $user = $this->curl($this->api_tv_url."user_create", $data);
        return array('id' => $user['id'], 'token' => $user['key']);
    }

    /**
     * @param $data array('id' => user_id_in_flussonic_system, 'max_sessions' => 5, 'name' => name, 'email' => email.test.ru)
     * necessary key in data is id, others are optional
     */
    public function deleteUser($data) {
        $this->curl($this->api_tv_url."user_delete", $data);
    }

    /**
     * @param  $data array('id' => user_id_in_flussonic_system, 'max_sessions' => 5, 'name' => name, 'email' => email.test.ru)
     * necessary key in data is id, others are optional
     * @return array('token' => token_for_this_user)
     */
    public function updateUser($data) {
        $user = $this->curl($this->api_tv_url."user_update", $data);
        return array('token' => $user['key']);
    }

    /**
     * @return array('host' => domain.ru, users => array (0 => array('id' => 1, 'key' => abc... )...)
     */
    public function getUsers() {
        return $this->curl($this->api_tv_url."users");
    }

    /**
     * @param string $channel_name optional, if no param, schedule for all channels
     * @return array('chanel_id' => array('about' => .. , 'start' => .., 'end' => .., 'name' => ..)..)
     */
    public function getSchedule($channel_name = '', $token) {
        $result = array();
        if($channel_name === '')
            $channels = $this->curl($this->api_tv_epg_url."all/epg.json".$token);
        else
            $channels = $this->curl($this->api_tv_epg_url."channel/{$channel_name}/epg.json".$token);
        foreach ($channels as $channel_name => $channel) {
            if($channel['events'] === NULL)
                continue;
            $result[$channel_name] = array();
            ksort($channel['events']);
            foreach($channel['events'] as $event) {
                foreach($event as $property) {
                    array_push($result[$channel_name], array(
                        'about' => $property['about'],
                        'start' =>  gmdate("Y-m-d H:i:s", $property['start']),
                        'clear_start' => gmdate("H:i", $property['start']), //добавил параметр что бы получать только время
                        'end' => gmdate("Y-m-d H:i:s", $property['start'] + $property['duration']),
                        'name' => $property['name'],

                    ));
                }
            }
        }
        return $result;
    }
    
    //заготовка для конвертации видео для последующей загрузки на сервер и создания сервера
    public function concatenateVideos($data, $channel_name) {
        //пример запроса для
/*      ffmpeg -i video1.avi video1.mp4 &&
        ffmpeg -i video1.mp4 -c copy video1.ts &&
        ffmpeg -i video.avi video.mp4 &&
        ffmpeg -i video.mp4 -c copy video.ts &&
        ffmpeg -i "concat:video.ts|video1.ts|" -c copy final.mp4*/
        //команду можно не собирать, передаем поток вывода к внешним объетам, чтобы exec не ждал ответа
        //shell_exec('ffmpeg -i video1.avi video1.mp4 1> block.txt 2>&1 &');
        //shell_exec('ffmpeg -i video1.avi video1.mp4 && ffmpeg -i video1.mp4 -c copy video1.ts && ffmpeg -i video.avi video.mp4 && ffmpeg -i video.mp4 -c copy video.ts && ffmpeg -i "concat:video.ts|video1.ts|" -c copy final.mp4');
        $path = smVideoHelper::getDir();
        $ts_command = "";
        $concat_command = 'ffmpeg -i "concat:';
        $last_key = key(array_slice($data, -1, 1, true));
        foreach ($data as $key => $value) {
            $full_path = $path.'/'.$value['contact_id'].'/'.$value['name'];
            $path_no_file_name = $path.'/'.$value['contact_id'].'/';
            $info = pathinfo($full_path);
            $file_name_no_extension = $path_no_file_name.$info['filename'];
            $ts_command .= "ffmpeg -i {$full_path} -acodec copy -vcodec copy -vbsf h264_mp4toannexb -f mpegts {$file_name_no_extension}.ts ";
            //$ts_command .= "ffmpeg -i {$full_path} -c copy {$file_name_no_extension}.ts ";
            $concat_command .= "{$file_name_no_extension}.ts";
            if ($key != $last_key) {
                $ts_command .= "&& ";
                $concat_command .= "|";
            }
        }
        $channel_full_name = $path_no_file_name.$channel_name;
        $concat_command .= '" -vcodec copy -acodec copy '.$channel_full_name.'.mp4';
        $api_url = str_replace('https://', '', $this->api_flussonic_url).'upload';
        $controller = wa()->getRouteUrl('sm/frontend/concatReady');
        $controller = substr($controller, 1);
        $url = wa()->getRootUrl(true).$controller;
        exec($_SERVER['DOCUMENT_ROOT']."/concat.py '{$ts_command}' '{$concat_command}' {$path_no_file_name} {$channel_name} {$data[0]['contact_id']} {$this->login} {$this->password} {$api_url} {$channel_full_name} {$url} > /dev/null &");
    }

    //загрузка видео
    public function uploadVideo() {
        $cFile = curl_file_create('wa-data/public/sm/videos/1/long_test.mp4', 'video/mp4', 'file');
        $post = array('file'=> $cFile);
        $ch = curl_init();
        $headers = array();
        $headers[] = 'X-Prefix: vod';
        $headers[] = 'X-Path: priv';
        $headers[] = 'X-Subpath: /';
        curl_setopt($ch, CURLOPT_URL,'https://stream.smotron.tv/flussonic/api/upload');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT,60);
        curl_setopt($ch, CURLOPT_USERPWD, "flussonic" . ":" . "letmein!");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $result=curl_exec ($ch);
        curl_close ($ch);
        return $result;
    }

    public function downloadEpg() {
        $video_folder = wa()->getDataPath('EPG', true, 'sm');
        exec("rm {$video_folder}/epg.xml.gz");
        exec("rm {$video_folder}/epg.xml");
        $url = "http://teleguide.info/download/smarthome/xmltv/xmltv.zip";
        $path = "$video_folder/epg.xml.gz";
        $fp = fopen($path, 'w');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        $data = curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        exec("gunzip {$path}");
    }

    public function getEpg($id, $date) {
        ini_set("memory_limit",-1);
        if(!$id) return 0;
        $epg_folder = wa()->getDataPath('EPG', true, 'sm');
        $smpl_xml = simplexml_load_file("{$epg_folder}/epg.xml");
        $result = array();
        if ($smpl_xml) {
            foreach($smpl_xml->programme as $channel) {
                $day_start = substr($channel['start'], 6, 2);
                $month_start = substr($channel['start'], 4, 2);
                $hour_start =substr($channel['start'], 8, 2);
                if ($channel['channel'] == $id && $month_start == $date['month'] && $day_start == $date['day'] && $hour_start >= $date['hour'] - 1) {

                    waLog::dump('!!!!!');

                    $year_start = substr($channel['start'], 0, 4);
                    $hour_start = substr($channel['start'], 8, 2);
                    $minutes_start = substr($channel['start'], 10, 2);

                    $year_stop = substr($channel['stop'], 0, 4);
                    $month_stop = substr($channel['stop'], 4, 2);
                    $day_stop = substr($channel['stop'], 6, 2);
                    $hour_stop = substr($channel['stop'], 8, 2);
                    $minutes_stop = substr($channel['stop'], 10, 2);

                    array_push($result, array(
                        'about' => $channel->desc."",
                        'start' =>  strtotime("{$year_start}-{$month_start}-{$day_start} {$hour_start}:{$minutes_start}"),
                        'end' => strtotime("{$year_stop}-{$month_stop}-{$day_stop} {$hour_stop}:{$minutes_stop}"),
                        'name' => $channel->title."",
                    ));
                }
            }
        }
        waLog::dump($result);
        return $result;
    }

    /**
     * @param $full_path string full path to video, that should be transcoded  example: /var/www/smotron/httpdocs/videos/1/test.avi
     * @param $final_name string full path with new name to new video after transcoding example: /var/www/smotron/httpdocs/videos/1/test.mp4
     * @param $progress_file string full path to file with output of ffmpeg, using this file we can calculate progress example: /var/www/smotron/httpdocs/videos/1/test.txt
     * @param $filename string file name without path, need for flussonic AS FINAL NAME, BUT NO PATH! NOT DEFAULT ONE!!!. example test.mp4 NOT test.avi !!!!
     * @param $contact_id int
     */
    public function transcoding($full_path, $final_name, $progress_file, $filename, $contact_id, $row_id) {
        $api_url = str_replace('https://', '', $this->api_flussonic_url).'upload';
        shell_exec($_SERVER['DOCUMENT_ROOT']."/transcoding.py {$full_path} {$final_name} {$progress_file} {$filename} {$contact_id} {$this->login} {$this->password} {$api_url} {$row_id}> /dev/null &");
        //shell_exec($command);
    }

    public function transcodingqueue() {
        shell_exec($_SERVER['DOCUMENT_ROOT']."/transcodingqueue.py > /dev/null &");
    }

    /**
     * @param int contact id
     * @param string video name
     * @return int percentage  of video transcoding or null if no such file
     */
    public function getTranscodingProgress($contact_id, $video_name) {
        $path = smVideoHelper::getDir();
        //file with progress
        $content = @file_get_contents($path.'/'.$contact_id.'/'.$video_name.'.txt');
        //$content = @file_get_contents('/var/www/smotron/httpdocs/wa-data/public/sm/videos/1/block.txt');
        if($content){
            //get duration of source
            preg_match("/Duration: (.*?), start:/", $content, $matches);
            $rawDuration = $matches[1];
            //rawDuration is in 00:00:00.00 format. This converts it to seconds.
            $ar = array_reverse(explode(":", $rawDuration));
            $duration = floatval($ar[0]);
            if (!empty($ar[1])) $duration += intval($ar[1]) * 60;
            if (!empty($ar[2])) $duration += intval($ar[2]) * 60 * 60;

            //get the time in the file that is already encoded
            preg_match_all("/time=(.*?) bitrate/", $content, $matches);

            $rawTime = array_pop($matches);

            //this is needed if there is more than one match
            if (is_array($rawTime)){$rawTime = array_pop($rawTime);}

            //rawTime is in 00:00:00.00 format. This converts it to seconds.
            $ar = array_reverse(explode(":", $rawTime));
            $time = floatval($ar[0]);
            if (!empty($ar[1])) $time += intval($ar[1]) * 60;
            if (!empty($ar[2])) $time += intval($ar[2]) * 60 * 60;

            //calculate the progress
            $progress = round(($time/$duration) * 100);
            return $progress;
        }
        return null;
    }
}