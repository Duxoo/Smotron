<?php

class smClientsEditCompanySaveController extends waJsonController
{
    public function execute()
    {
        $company = waRequest::post('company', null);
        if(!is_array($company)) {$this->response = array('result' => 0, 'message' => 'Системная ошибка #NOARR'); return;}

        $contact_id = waRequest::post('id', null);
        //$contact_id = wa()->getUser()->getId();
        $contact_model = new waContactModel();
        $contact_data = $contact_model->getById($contact_id);
        if(!$contact_data) {$this->response = array('result' => 0, 'message' => 'Системная ошибка: неидентифицированный id'); return;}

        $user_company_model = new smUserCompanyModel();
        $user_company_model->setData($contact_id, $company);

        $this->response = array('result' => 1, 'message' => 'Данные сохранены');
    }
}