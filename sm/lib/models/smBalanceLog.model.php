<?php

class smBalanceLogModel extends waModel
{
    protected $table = 'sm_balance_log';

    public function getLog($contact_id, $ask = false)
    {
        $query = 'SELECT * FROM '.$this->table.' AS smbl WHERE smbl.contact_id = ? ORDER BY datetime';
        if(!$ask){$query .= '  DESC';}
        return $this->query($query,$contact_id);
    }
}