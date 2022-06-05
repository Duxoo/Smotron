<?php

class smClientsEditSaveController extends waJsonController
{
    public function execute()
    {
        //if(!wa()->getUser()->isAuth()) {$this->response = array('result' => 0, 'message' => 'Необходимо войти в систему'); return;}
        $profile = waRequest::post('profile', null);
        if(!is_array($profile)) {$this->response = array('result' => 0, 'message' => 'Системная ошибка #NOARR'); return;}

        $contact_id = waRequest::post('id', null);
        $contact_model = new waContactModel();
        $contact_data = $contact_model->getById($contact_id);
        if(!$contact_data) {$this->response = array('result' => 0, 'message' => 'Системная ошибка: неидентифицированный id'); return;}

        $name = trim(ifempty($profile['name'], ''));
        if(!mb_strlen($name)) {$this->response = array('result' => 0, 'message' => 'Никнейм не может быть пустым'); return;}

        /*
        $email = trim(ifempty($profile['email'], ''));
        if(!mb_strlen($email)) {$this->response = array('result' => 0, 'message' => 'Указан некорректный email'); return;}
        $validator = new waEmailValidator();
        if(!$validator->isValid($email)) {$this->response = array('result' => 0, 'message' => 'Указан некорректный email'); return;}
        */
        $contact = new waContact($contact_id);
        //$contact = wa()->getUser();
        $contact->set('firstname', $name);
        //$contact->set('email', $email);
        /*if(isset($profile['change_password']))
        {
            $contact->set('password', $new_password);
        }*/
        $contact->save();
        $this->response = array('result' => 1, 'message' => 'Данные сохранены', 'name' => htmlspecialchars($name, ENT_QUOTES));
    }
}