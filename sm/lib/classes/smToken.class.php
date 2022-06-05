<?php

class smToken
{
    protected $smfl_user_model;

    /**
     * @var smFlussonicApi
     */
    protected $api;

    protected $params = array(
        'name' => null,
        'email' => null,
        'max_sessions' => 1,
    );

    public function __construct()
    {
        $this->smfl_user_model = new smFluserSmuserModel();
        $this->api = new smFlussonicApi();
    }

    /**
     * @param $user = array('name'=>'user_name','email'=>'user_email')
     */
    public function createUserToken($user)
    {
        $this->params['name'] = $user['name'];
        $this->params['email'] = $user['email'][0]['value'];
        $api_response = $this->api->createUser($this->params);
        $result['sm_user_id'] = $user['sm_user_id'];
        if($api_response['id'] === null){
            return $result.' fl_id is null';
        }
        if(isset($api_response['id']) & isset($api_response['token']))
        {
            $result['fl_user_id'] = $api_response['id'];
            $result['fl_token'] = $api_response['token'];
        }
        $this->smfl_user_model->insert($result);
        return $result;
    }

    /**
     * @param $user_id
     * @param int $max_sessions
     */
    public function updateUserToken($user_id, $max_sessions = 1)
    {
        $user = $this->smfl_user_model->getById($user_id);
        $new_token = $this->generateToken();
        $this->api->updateUser(array('id' => $user['fl_user_id'], 'key' => $new_token, 'max_sessions' => $max_sessions));//$user['fl_token']);
        $this->smfl_user_model->updateById($user_id,array('fl_token' => $new_token));
    }

    /**
     * @param $user_id
     */
    public function deleteUserToken($user_id)
    {
        $user = $this->smfl_user_model->getById($user_id);
        $this->smfl_user_model->deleteById($user_id);
        $this->api->deleteUser($user['fl_user_id']);
    }

    public function generateToken()
    {
        $length = 20;
        $alphabet = "abcdefghijklmnopqrstuvwxyz1234567890";
        $result = '';
        while(strlen($result) < $length) {$result .= $alphabet{mt_rand(0, strlen($alphabet)-1)};}
        return $result;
    }
}