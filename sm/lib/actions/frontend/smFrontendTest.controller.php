<?php

class smFrontendTestController extends waJsonController {

    public function execute()
    {
        $obj = new smFlussonicApi();
        $obj->getOriginalVideoSourceUrl("five", "Qd7dNt6S71");
/*        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://b2btestservice.ocs.ru/b2bJSON.asmx/GetCatalog");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT,60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        $payload = json_encode( array(
            'Login' => 'К0151375',
            'Token' => 'YPsJInaadc12sdq12xc',
            ));
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload);
        $obj = curl_exec($ch);
        curl_close($ch);
        $obj = json_decode($obj, true);*/

        /*smSubscribtionHelper::getUpgradeOptions(1,699,'2020-06-25 20:40:18','2020-07-25 20:29:36');*/

        /*$this->response = $obj->updateUser(array('id' => '37', 'key' => '1234'));*/
        /*$this->response = $obj->getUsers();*/
/*        exec("rm wa-data/public/sm/EPG/epg.xml.gz");
        exec("rm wa-data/public/sm/EPG/epg.xml");
        $url = "http://teleguide.info/download/smarthome/xmltv/xmltv.zip";
        $path = 'wa-data/public/sm/EPG/epg.xml.gz';
        $fp = fopen($path, 'w');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        $data = curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        exec("gunzip {$path}");*/
/*        $obj->concatenateVideos(array(
            0 => array(
                'contact_id' => '1',
                'name' => 'aasdsa.mp4',
            ),
            1 => array(
                'contact_id' => '1',
                'name' => 'vidosik.mp4',
            ),
            2 => array(
                'contact_id' => '1',
                'name' => 'zhopa.mp4',
            ),
            3 => array(
                'contact_id' => '1',
                'name' => 'testeeee.mp4',
            ),
            4 => array(
                'contact_id' => '1',
                'name' => 'testss.mp4',
            ),
        ), 'em_nu_da');*/
        /*
         *
         * ЭТО ВСЕ ТЕСТЫ ДЛЯ ТРАНСКОДИРОВАНИЯ
         *
         */
        //shell_exec("/var/www/smotron/httpdocs/test.py");
        //echo $obj->getTranscodingProgress(1, 'long_test');

/*        $content = @file_get_contents('/var/www/smotron/httpdocs/wa-data/public/sm/videos/1/block.txt');

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

            echo "Duration: " . $duration . "<br>";
            echo "Current Time: " . $time . "<br>";
            echo "Progress: " . $progress . "%";

        }*/





        /*
         *
         * ЭТО ВСЕ ТЕСТЫ ДЛЯ ФЛЮССОНИКА
         *
         */
        //$this->response = $obj->uploadVideo();
        //$this->response = $obj->getEpg();
        //$this->response = $obj->getFlussonicConfiguration();
        //$this->response = $obj->createUser(array('name' => 'proverka', 'email' => 'provarrka@ma.ru', 'max_sessions' => 3));
        //$this->response = $obj->updateUser(array('id' => 2, 'name' => 'update_check'));
        //$obj->deleteUser(array('id' => 3));
        //$this->response = $obj->getUsers();
        //$this->response = $obj->getSchedule();
/*        waLog::dump($_POST, "0.log");
        if ( 0 < $_FILES['file']['error'] ) {
            echo 'Error: ' . $_FILES['file']['error'] . '<br>';
        }
        else {
            move_uploaded_file($_FILES['file']['tmp_name'], 'uploads/' . $_FILES['file']['name']);
        }
        $this->response = $obj->uploadVideo();*/
//"transcoder":{"global":{"hw":"nvenc"},"audio":{"bitrate":64000,"codec":"aac"},"video":{"0":{"bitrate":1000000,"codec":"h264"}}}
        //$this->response = $obj->updateConfig('{"streams": {"2x2": {"meta": {"iptv": "true"}, "urls": [{"url": "file://vod/f2.mp4"}], "auth": {"url": "iptv://localhost"}}}}');
        //$this->response = $obj->updateConfig(array('name' => '2x2', 'url' => 'udp://224.200.202.171:1234/172.25.252.6'));
        //$this->response = $obj->updateConfig(array('name' => 'long_test', 'url' => 'file://vod/long_test.mp4'));
        /*        $this->response = $obj->createUpdateStream(array(
            'name' => '2x2',
            'path' => 'udp://224.200.202.171:1234/172.25.252.6'
        ));*/
        // 2x2
        //udp://224.200.202.171:1234/172.25.252.6
        // file://vod/f2.mp4
    }
}