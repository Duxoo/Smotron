<?php
class smRestApi
{
    public function processCommand($parameters)
    {
        $command = ifempty($parameters['command'], '');

        switch($command)
        {
            //Команда получения epg канала, в случае если канал не указан в параметре, функция вернет епг по всем каналам.
            case 'getChannelEpg':
                return $this->commandGetChannelEpg($parameters);
                break;
            //Команда получения списка каналов пользователя
            case 'getUserChannels':
                return $this->commandGetUserChannels($parameters);
                break;
            // Команды с проверкой ключа внешнего источника
            case 'trustedAuth':
                return $this->commandTrustedAuth($parameters);
                break;
            // Сервисные команды
            case 'sysTime':
                return $this->commandSysTime($parameters);
                break;
            // Проверка интернета
            case 'checkInternet':
                return $this->commandCheckInternet();
                break;
            default:
                return array('result' => 0, 'message' => 'ERROR: ILLEGAL COMMAND');
                break;
        }
        return array('result' => 0, 'message' => 'ERROR: ILLEGAL COMMAND');
    }

    /////////////////////////////////////////////////////////////////////////////////////////////
    // COMMANDS
    /////////////////////////////////////////////////////////////////////////////////////////////

    public function commandGetChannelEpg($parameters)
    {
        $session = $this->getSession($parameters);
        if(!$session) {return array('result' => 0, 'message' => 'ERROR: SESSION NOT VALID');}

        $session_model = new smRestSessionModel();
        $session_data = $session_model->getById($parameters['session_code']);

        $flApi = new smFlussonicApi();

        $channelModel = new smChannelModel();
        $channelInfo = $channelModel->getByField('fl_channel_name', $parameters['channel_id']);
        $time = date('Y-m-d H:i:s',time());
        $date_array = date_parse_from_format('Y-m-d H:i:s', $time);

        if(isset($parameters['channel_id'])){ $epg = $flApi->getEpg($channelInfo['epg_id'], $date_array);}
        else{$epg = $flApi->getEpg($parameters['channel_id'], $date_array);}
        waLog::dump($date_array);
        waLog::dump($epg);
        return array('result' => 1, 'epg' => $epg);
    }

    public function getSession($parameters)
    {
        $code = ifempty($parameters['session_code'], 0);
        $session_model = new smRestSessionModel();
        return $session_model->getById($code);
    }

    public function commandGetUserChannels($parameters)
    {
        $session = $this->getSession($parameters);
        if(!$session) {return array('result' => 0, 'message' => 'ERROR: SESSION NOT VALID');}

        $session_model = new smRestSessionModel();
        $session_data = $session_model->getById($parameters['session_code']);

        $subuser = new smSubuser($session_data['subuser_id']);
        $channels = $subuser->getChannels();
        if(!$channels) {return array('result' => 0, 'message' => 'ERROR: COULD NOT FIND CHANNEL');}

        $token = $subuser->getToken();
        if(!$token) {return array('result' => 0, 'message' => 'ERROR: TOKEN IS NULL');}

        $flApi = new smFlussonicApi();

        $channels_url = array();
        foreach ($channels as $key => $value)
        {
            array_push($channels_url, array('channel_id' => $value['fl_channel_name'], 'channel_name' => $value['name'], 'source' => $flApi->getOriginalVideoSourceUrl($value['fl_channel_name'], $token), 'preview' => $flApi->getPreview($value['fl_channel_name'], $token)));
        }
        waLog::dump($channels_url);
        return array('result' => 1, 'channels' => $channels_url);
    }

    public function validateLogin($login)
    {
        /*$email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {return $email;}
        else{return null;}*/
        $regexp = "/^U100[0-9]{1,}_[A-Za-z0-9]{1,}/";
        if(preg_match($regexp,$login)){return $login;}
        else{return null;}

    }

    public function verifyUserAgent($parameters)
    {
        if(!isset($parameters['s'])) {return array('result' => 0, 'message' => 'ERROR: SIGNATURE NOT FOUND');}
        if(!isset($parameters['t'])) {return array('result' => 0, 'message' => 'ERROR: TIMESTAMP NOT FOUND '.time());}
        if(!isset($parameters['login'])) {return array('result' => 0, 'message' => 'ERROR: login NOT FOUND');}

        $timestamp = intval($parameters['t']);
        $time = time();
        if($timestamp < $time - 7200 || $timestamp > $time + 7200) {return array('result' => 0, 'message' => 'ERROR: INVALID TIMESTAMP');}

        $subuser_model = new smSubuserModel();
        $agent_data = $subuser_model->getByField('login',$parameters['login']);
        if(!$agent_data) {return array('result' => 0, 'message' => 'ERROR: login NOT VALID');}

        waLog::dump($agent_data);
        waLog::dump($parameters);

        $request_data = $parameters;
        unset($request_data['s']);
        unset($request_data['command']);
        unset($request_data['t']);
        ksort($request_data);

        $signature = $parameters['t'];
        foreach($request_data as $key => $field) {$signature .= $field;}
        $signature .= $agent_data['password'];

        waLog::dump($signature);

        if($parameters['s'] != md5($signature)) {return array('result' => 0, 'message' => 'ERROR: INVALID SIGNATURE SHOULD BE '.md5($signature));}

        return array('result' => 1, 'message' => 'SUCCESS: SIGNATURE IS VALID');
    }

    public function commandTrustedAuth($parameters)
    {
        waLog::dump($parameters);

        $verification = $this->verifyUserAgent($parameters);
        if(!$verification['result']) {return $verification;}

        // login format U100{parent_id}_{name}
        $login = $this->validateLogin(ifempty($parameters['login'], ''));
        if(!$login) {return array('result' => 0, 'message' => 'ERROR: INVALID LOGIN');}

        $subuser_model = new smSubuserModel();
        $subuser_data = $subuser_model->query("SELECT * FROM ".$subuser_model->getTableName()."
													WHERE login = s:login ORDER BY id ASC LIMIT 1", array('login' => $login))->fetchAll();
        if(!count($subuser_data)) {$subuser_data = null;} else {$subuser_data = $subuser_data[0];}
        if(!$subuser_data)
        {
            /*$contact = new waContact();
            $contact->set('phone', $phone);
            $contact->save();
            $contact_id = $contact->getId();*/
            return array('result' => 0, 'message' => 'ERROR: COULD NOT FIND USER');
        }
        else
        {
            $subuser_id = $subuser_data['id'];
        }

        $session_model = new smRestSessionModel();
        $session_code = $session_model->createTrustedSession($subuser_id, $login);
        $session_data = $session_model->getById($session_code);
        if(!$session_data) {return array('result' => 0, 'message' => 'ERROR: COULD NOT CREATE SESSION');}

        return array('result' => 1, 'message' => 'SUCCESS: HERE IS YOUR SESSION', 'session_code' => $session_data['session_code']);
    }

    public function commandSysTime($parameters)
    {
        return array('result' => 1, 'message' => 'SUCCESS: HERE IS THE TIME', 'timestamp' => time()-3, 'datetime' => date('Y-m-d H:i:s', (time()-3)));
    }

    public function commandCheckInternet()
    {
        return "1";
    }


}
