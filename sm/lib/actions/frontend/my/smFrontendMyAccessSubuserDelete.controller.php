<?php

class smFrontendMyAccessSubuserDeleteController extends waJsonController
{
    public function execute()
    {
        if(!wa()->getUser()->isAuth()) {$this->response = array('result' => 0, 'message' => _w("You must log in")); return;}
        $sub_user_id = waRequest::param('sub_user_id', null);
        if(!isset($sub_user_id)) {$this->response = array('result' => 0, 'message' => _w("System error #NOARR")); return;}
        $contact_id = wa()->getUser()->getId();
        $contact_model = new waContactModel();
        $contact_data = $contact_model->getById($contact_id);
        if(!$contact_data) {$this->response = array('result' => 0, 'message' => _w("You must log in")); return;}

        $sub_user_model = new smSubuserModel();
        $sub_user_data = $sub_user_model->getById($sub_user_id);
        if(!isset($sub_user_data)){$this->response = array('result' => 0, 'message' => _w("System error #NOARR")); return;}
        if($sub_user_data['parent_contact_id'] != $contact_id){$this->response = array('result' => 0, 'message' => _w("Deletion error")); return;}

        $sub_channel_model = new smSubuserChannelsModel();

        $sub_user_model->deleteById($sub_user_id);
        $sub_channel_model->deleteByField('subuser_id',$sub_user_id);
        $this->response = array('result' => 1);
    }
}