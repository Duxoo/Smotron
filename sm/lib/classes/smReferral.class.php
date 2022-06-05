<?php

class smReferral
{
    protected $id, $code, $data;
    protected $model;

    public function __construct($id = null, $code = null)
    {
        $this->model = new smReferralModel();
        $this->id = $id;
        waLog::dump("b");
        if($id)
        {
            waLog::dump("a");
            $this->data = $this->model->getById($id);
            waLog::dump($this->data);
            if(!isset($this->data)){
                if(isset($code)){
                    $parent = $this->getRowByCode($code);
                    waLog::dump($parent);
                    $current_data = array(
                        'contact_id' => $id,
                        'parent_id' => $parent['contact_id'],
                        'referral_code' => $this->createReferralCode(),
                        'left_key' => $parent['right_key'],
                        'right_key' => $parent['right_key'] + 1,
                        'depth' => $parent['depth'] + 1,
                    );
                    //$this->data = $this->model->createNewNode($current_data);
                }
                else
                {
                    $current_data = array(
                        'contact_id' => $id,
                        'parent_id' => 0,
                        'referral_code' => $this->createReferralCode(),
                        'depth' => 0,
                    );

                    //$this->data = $this->model->createNewNode($current_data);
                }
                $this->data = $this->model->createNewNode($current_data);
                $this->data = $current_data;
            }
        }
    }

    public function getCode()
    {
        return $this->data['referral_code'];
    }

    public function createNode()
    {
        $this->data = $this->model->createNewNode($this->data);
    }

    public function getData()
    {
        return $this->data;
    }

    public function checkCode()
    {
        $data = $this->model->getByField('referral_code', $this->code);
        return (is_array($data)) ? true : false;
    }

    public function getRowByCode($code = null)
    {
        if($code == null){ return null; }
        return $this->model->getByField('referral_code', $code);
    }

    protected function createReferralCode()
    {
        $length = 20;
        $alphabet = "abcdefghijklmnopqrstuvwxyz1234567890";
        $result = 'r35';
        while(strlen($result) < $length) {$result .= $alphabet{mt_rand(0, strlen($alphabet)-1)};}
        return $result;
    }

    public function getTree()
    {
        $collection = $this->model->getCurrentBranch($this->data);

        $trees = [];
        $l = 0;

        if (count($collection) > 0) {
            $stack = [];

            foreach ($collection as $node) {
                $item = $node;
                $item['childs'] = [];

                $l = count($stack);
                unset($item['password']);
                unset($item['login']);

                $contact = new waContact($item['contact_id']);
                $item['img_url'] = $contact->getPhoto();

                while($l > 0 && $stack[$l - 1]['depth'] >= $item['depth']) {
                    array_pop($stack);
                    $l--;
                }

                if ($l == 0) {
                    $i = count($trees);
                    $trees[$i] = $item;
                    $stack[] = & $trees[$i];
                } else {
                    $i = count($stack[$l - 1]['childs']);
                    $stack[$l - 1]['childs'][$i] = $item;
                    $stack[] = & $stack[$l - 1]['childs'][$i];
                }
            }
        }
        return $trees;
    }

    public function getParents()
    {
        $data = $this->model->getParents($this->data);
        $data = array_reverse($data);
        $data = array_slice($data,1,5);
        return $data;
    }
}