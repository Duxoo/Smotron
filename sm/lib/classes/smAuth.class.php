<?php

class smAuth
{
    public function subAuth($auth_data)
    {
        $model = new smSessionsModel();
        $auth_data['login_hash'] =  md5($auth_data['login']);
        $auth_data['password'] =  self::getPasswordHash($auth_data['password']);
        $uuid = $model->createSession($auth_data, self::getSalt());

        setcookie('uuid', $uuid,time()+36000,'/');
        setcookie('login', $auth_data['login_hash'],time()+36000,'/');
    }

    public function isSubAuth()
    {
        $ses_model = new smSessionsModel();
        $sub_user_model = new smSubuserModel();

        $auth_data['login_hash'] = waRequest::cookie("login");
        $auth_data['uuid'] = waRequest::cookie("uuid");
        $auth_data['login'] = $ses_model->getLogin($auth_data['uuid']);

        /*waLog::dump($auth_data);*/

        $subuser = $sub_user_model->getByField('login',$ses_model->getLogin($auth_data['uuid']));
        $hash = md5($auth_data['login']).md5(self::getSalt()).$subuser['password'];
        $session = $ses_model->getById($auth_data['uuid']);
        //waLog::dump($subuser);
        //waLog::dump($session);
        if(!$this->checkEndtime($session['endtime'])){return array('result' => 0, 'message' => 'session is close');}

        //$ses_model->getLogin($auth_data['uuid'])

        if($ses_model->checkUuid($auth_data['uuid'],$hash)){ return array('result' => $ses_model->checkUuid($auth_data['uuid'], $hash), 'message' => 'ok', 'login' => $auth_data['login']);}
        else{return array('result' => $ses_model->checkUuid($auth_data['uuid'], $hash), 'message' => 'Error: password or login');}
        //return array('result' => $ses_model->checkUuid($auth_data['uuid'], $hash), 'message' => 'ok');

        /*}
        else
        {
            //$ses_model->getLogin($auth_data['uuid'])
            $subuser = $sub_user_model->getByField('login',$ses_model->getLogin($auth_data['uuid']));
            $hash = md5($auth_data['login']).md5(self::getSalt()).$subuser['password'];

            return array('result' => $ses_model->checkUuid($auth_data['uuid'], $hash), 'message' => 'ok');
        }*/
    }

    public function clearSession($uuid)
    {
        $model = new smSessionsModel();
        $model->deleteById($uuid);
    }

    public function getSubuserData($field = null)
    {
        $ses_model = new smSessionsModel();
        $sub_user_model = new smSubuserModel();

        $auth_data['login_hash'] = waRequest::cookie("login");
        $auth_data['uuid'] = waRequest::cookie("uuid");
        $auth_data['login'] = $ses_model->getLogin($auth_data['uuid']);

        $subuser = $sub_user_model->getByField('login',$ses_model->getLogin($auth_data['uuid']));
        if(!isset($subuser[$field])){return $subuser;}
        else{return $subuser[$field];}
    }

    public function checkEndtime($session_endtime)
    {
        /*waLog::dump($session_endtime);*/
        if($session_endtime < date("Y-m-d H:i:s", strtotime("now"))){return false;}
        else{return true;}
    }

    protected static function getSalt()
    {
        return 'salt';
    }

    public static function getPasswordHash($password)
    {
        $salt = self::getSalt();
        if (function_exists('wa_password_hash')) {
            return wa_password_hash($password.$salt);
        } else {
            return md5($password.$salt);
        }
    }

}