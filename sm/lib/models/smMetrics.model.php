<?php

class smMetricsModel extends waModel
{
    protected $table = 'sm_metrics';

    public function increment($type, $amount = 0, $data = array())
    {
        $data['type'] = $type;

        if(empty($data['session'])){
            $data['session'] = waRequest::cookie('PHPSESSID');
        }

        if(empty($data['amount'])){
            $data['amount'] = $amount;
        }

        if (empty($data['contact_id'])) {
            $data['contact_id'] = wa()->getUser()->getId();
        }

        //$curent = $this->getByField(array('type' => $data['type'], 'session' => $data['session'], 'date' => $data['date']));
        /*if (isset($curent)) {
            return true;
        }*/

        $time = time();
        $data['year'] = date('Y', $time);
        $data['month'] = date('m', $time);
        $data['quarter'] = floor((date('n', $time) - 1) / 3) + 1;
        $data['date'] = date('Y-m-d', $time);

        $new = 1;
        $event_model = new smUserEventModel();

        $row = $event_model->getByField('user_id',$data['contact_id']);
        if(isset($row['user_id'])){ $new = 0; }
        if(is_array($data)){$sales_model = new smMetricsSalesModel(); $sales_model->change($data, $new);}

        return $this->insert($data);
    }


}