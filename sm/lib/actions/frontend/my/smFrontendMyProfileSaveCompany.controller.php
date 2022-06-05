<?php

class smFrontendMyProfileSaveCompanyController extends waJsonController
{
    public function execute()
    {
		if(!wa()->getUser()->isAuth()) {$this->response = array('result' => 0, 'message' => _w("You must log in")); return;}
		$company = waRequest::post('company', null);
		if(!is_array($company)) {$this->response = array('result' => 0, 'message' => _w("System error #NOARR")); return;}
		
		$contact_id = wa()->getUser()->getId();
		$contact_model = new waContactModel();
		$contact_data = $contact_model->getById($contact_id);
		if(!$contact_data) {$this->response = array('result' => 0, 'message' => _w("You must log in")); return;}
		
		$user_company_model = new smUserCompanyModel();
		$user_company_model->setData($contact_id, $company);
		
		$this->response = array('result' => 1, 'message' => _w("Data is saved"));
    }
}