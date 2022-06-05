<?php

class smFrontendMyProfileSaveController extends waJsonController
{
    public function execute()
    {
		if(!wa()->getUser()->isAuth()) {$this->response = array('result' => 0, 'message' => _w("You must log in")); return;}
		$profile = waRequest::post('profile', null);
		if(!is_array($profile)) {$this->response = array('result' => 0, 'message' => _w("System error #NOARR")); return;}
		
		$contact_id = wa()->getUser()->getId();
		$contact_model = new waContactModel();
		$contact_data = $contact_model->getById($contact_id);
		if(!$contact_data) {$this->response = array('result' => 0, 'message' => _w("You must log in")); return;}
		
		$name = trim(ifempty($profile['name'], ''));
		if(!mb_strlen($name)) {$this->response = array('result' => 0, 'message' => _w("The nickname can't be empty")); return;}
		
		/*
		$email = trim(ifempty($profile['email'], ''));
		if(!mb_strlen($email)) {$this->response = array('result' => 0, 'message' => 'Указан некорректный email'); return;}
		$validator = new waEmailValidator();
		if(!$validator->isValid($email)) {$this->response = array('result' => 0, 'message' => 'Указан некорректный email'); return;}
		*/
		
		if(isset($profile['change_password']))
		{
			$password = waContact::getPasswordHash(ifempty($profile['password'], ''));
			if($password != $contact_data['password']) {$this->response = array('result' => 0, 'message' => _w("The current password was entered incorrectly")); return;}
		
			$new_password = ifempty($profile['npassword'], '');
			$new_password_check = ifempty($profile['npassword2'], '');
			if(mb_strlen($new_password) < 5) {$this->response = array('result' => 0, 'message' => _w("The new password must contain at least five characters")); return;}
			if($new_password != $new_password_check) {$this->response = array('result' => 0, 'message' => _w("The new passwords you entered don't match")); return;}
		}
		
		$contact = wa()->getUser();
		$contact->set('firstname', $name);
		//$contact->set('email', $email);
		if(isset($profile['change_password']))
		{
			$contact->set('password', $new_password);
		}
		$contact->save();
		$this->response = array('result' => 1, 'message' => _w("Data is saved"), 'name' => htmlspecialchars($name, ENT_QUOTES));
    }
}