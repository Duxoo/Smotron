<?php

class smRestSessionModel extends waModel
{
    protected $table = 'sm_rest_session';
    protected $id = 'session_code';

    public function createTrustedSession($subuser_id, $login)
    {
        return $this->createSession($subuser_id, $login);
    }

    public function createSession($subuser_id, $login)
    {
        $data = array(
            'login' => $login,
            'subuser_id' => $subuser_id,
        );
        $query = "insert into ".$this->table." (session_code, subuser_id, login) values (uuid(),i:subuser_id,s:login)";
        $this->query($query, $data);
        return self::getUuid($data);
    }

    public function getUuid($data)
    {
        $field = $this->getByField($data);
        return $field['session_code'];
    }
}