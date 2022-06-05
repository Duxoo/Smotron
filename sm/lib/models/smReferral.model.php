<?php

class smReferralModel extends waModel
{
    protected $table = 'sm_contact_referral';
    protected $id = 'contact_id';

    public function createNewNode($data)
    {
        if($data['depth'] < 0){ return null; }
        if($data['depth'] == 0){
            $query = 'SELECT * FROM '.$this->table.'  
                      WHERE right_key = (SELECT MAX(right_key) FROM '.$this->table.')';
            $maxR = $this->query($query)->fetch();
            $maxR['contact_id'] = 0;
            $maxR['depth'] = 0;
            $data['left_key'] = $maxR['right_key'] + 1;
            $data['right_key'] = $maxR['right_key'] + 2;
            $row = $this->insertNewParentNode($data, $maxR);
        }
        if (isset($data['depth']) && $data['depth'] > 0)
        {
            $query = 'SELECT * FROM '.$this->table.'  
                      WHERE contact_id = i:parent_id';
            $parent = $this->query($query, $data)->fetch();
            $row = $this->insertNewNode($data, $parent);
        }
        return $row;
    }

    protected function insertNewNode($data, $parent)
    {
        $update_r = 'UPDATE '.$this->table.' SET right_key = right_key + 2 WHERE right_key >= i:right_key';
        $update_l = 'UPDATE '.$this->table.' SET left_key = left_key + 2 WHERE left_key > i:right_key';
        $insert_q = 'INSERT INTO '.$this->table.' (contact_id, parent_id, referral_code, left_key, right_key, depth) 
                     VALUES (i:contact_id, i:parent_id, s:referral_code, i:left_key, i:right_key ,i:depth)';
        try{
            $this->exec($update_r, $parent);
            $this->exec($update_l, $parent);
            $this->exec($insert_q, $data);
            return $this->getById($data['contact_id']);
        }catch (waException $e)
        {
            waLog::dump($e->getMessage(),'sm/referral/error/insert_error.log');
            return null;
        }
    }

    protected function insertNewParentNode($data, $parent)
    {
        $update_r = 'UPDATE '.$this->table.' SET right_key = right_key WHERE right_key >= i:right_key';
        $update_l = 'UPDATE '.$this->table.' SET left_key = left_key WHERE left_key > i:right_key';
        $insert_q = 'INSERT INTO '.$this->table.' (contact_id, parent_id, referral_code, left_key, right_key, depth) 
                     VALUES (i:contact_id, i:parent_id, s:referral_code, i:left_key, i:right_key ,i:depth)';
        try{
            $this->exec($update_r, $parent);
            $this->exec($update_l, $parent);
            $this->exec($insert_q, $data);
            return $this->getById($data['contact_id']);
        }catch (waException $e)
        {
            waLog::dump($e->getMessage(),'sm/referral/error/insert_error.log');
            return null;
        }
    }

    public function getFullTree()
    {
        return $this->query(' SELECT * FROM '.$this->table.' ORDER BY left_key')->fetchAll();
    }

    public function getParents($data)
    {
        if(!$this->checkNode($data)){ $this->createNewNode($data); }
        return $this->query(' SELECT * FROM '.$this->table.' WHERE left_key <= i:left_key AND right_key >= i:right_key ORDER BY left_key',$data)->fetchAll();
    }

    public function getChilds($data)
    {
        if(!$this->checkNode($data)){ $this->createNewNode($data); }
        return $this->query(' SELECT * FROM '.$this->table.' WHERE left_key >= i:left_key AND right_key <= i:right_key ORDER BY left_key',$data)->fetchAll();
    }

    public function getCurrentBranch($data)
    {
        if(!$this->checkNode($data)){ $this->createNewNode($data); }
        return $this->query(' SELECT * FROM '.$this->table.' AS scr LEFT JOIN wa_contact AS wc ON scr.contact_id = wc.id WHERE scr.left_key >= i:left_key AND scr.right_key <= i:right_key ORDER BY scr.left_key',$data)->fetchAll();
    }

    protected function checkNode($data)
    {
        $node = $this->getById($data['contact_id']);
        if(isset($node)){ return true;}
        else{ return false;}
    }
}