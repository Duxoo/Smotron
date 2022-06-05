<?php

class smFrontendMyCustomChannelsListController extends waJsonController
{
    public function execute()
    {
        if(!wa()->getUser()->isAuth()) {return array();}
        $user_id = wa()->getUser()->getId();

        $draw = intval(waRequest::get('draw'));
        $start = 0;
        if(waRequest::get('start')) {$start = intval(waRequest::get('start'));}
        $length = 25;
        if(waRequest::get('length')) {$length = intval(waRequest::get('length')); if($length > 50 || $length < 0) {$length = 50;}}

        $order = null;
        $column = 0;
        $direction = 'ASC';
        $search = null;
        if(isset($_GET['search']['value'])) {$search = $_GET['search']['value'];}
        if(waRequest::get('order'))
        {
            $order = 1;
            if(isset($_GET['order'][0]['column'])) {$column = intval($_GET['order'][0]['column']);}
            if(isset($_GET['order'][0]['dir']))
            {
                $direction = $_GET['order'][0]['dir'];
                if($direction == 'desc') {$direction = 'DESC';}
                else {$direction = 'ASC';}
            }
        }

        $channels_model = new smChannelModel();
        $result = $channels_model->listCustom($start, $length, $order, $column, $direction, $search, $user_id);

        $result['draw'] = $draw;
        $this->response = $result;
    }

    public function display()
    {
        $this->getResponse()->sendHeaders();
        if (!$this->errors) {
            $data = $this->response;
            echo json_encode($data);
        } else {
            echo json_encode(array('status' => 'fail', 'errors' => $this->errors));
        }
    }
}