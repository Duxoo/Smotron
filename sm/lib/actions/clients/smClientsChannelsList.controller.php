<?php

class smClientsChannelsListController extends waJsonController
{
    public function execute()
    {
        if(!wa()->getUser()->isAuth()) {return array();}
        $user_id = waRequest::get('id', null);

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

        $channels_model = new smChannelModel();
        $result = $channels_model->backendListCustom($start, $length, $order, $column, $direction, $search, $user_id);

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