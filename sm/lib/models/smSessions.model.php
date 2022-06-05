<?php

class smSessionsModel extends waModel
{
    protected $table = 'sm_active_session';
    protected $id = 'session_id';

    public function createSession($auth_data, $salt)
    {
        $hash = $auth_data['login_hash'].md5($salt).$auth_data['password'];
        $sub_user_model = new smSubuserModel();
        $sub_user_data = $sub_user_model->getByField('login', $auth_data['login']);

        $lenght = strpos($auth_data['login'],'_') - 4;
        $contact_id = substr($auth_data['login'],4,$lenght);

        $user = new smUser($contact_id);
        $user_data['endtime'] = $user->getData('subscribtion_valid_till');
        waLog::dump($auth_data);
        waLog::dump($user->getData('subscribtion_valid_till'));
        //$user_data['endtime']
        /*$user_model = new smContactTariffModel();*/
        /*$user_data = $user_model->getById($sub_user_data['parent_contact_id']);*/
        $query = "insert into ".$this->table." (session_id, hash, login, endtime) values (uuid(),'".filter_var($hash, FILTER_SANITIZE_FULL_SPECIAL_CHARS)."', '".filter_var($auth_data['login'],FILTER_SANITIZE_FULL_SPECIAL_CHARS)."', '".$user_data['endtime']."')";
        $this->query($query);
        return self::getUuid($hash);
    }

    public function getUuid($hash)
    {
        $field = $this->getByField('hash',$hash);
        return $field['session_id'];
    }

    public function checkUuid($uuid, $hash)
    {
        $row = $this->getById($uuid);
        if(isset($row))
        {
            if($row['hash'] == $hash)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    public function getLogin($uuid)
    {
        $row = $this->getById($uuid);
        return $row['login'];
    }
}