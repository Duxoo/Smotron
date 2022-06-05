<?php

class smSettingsAction extends waViewAction
{
    public function execute()
    {
        $this->setLayout(new smBackendLayout());
		$settings_model = new smSettingsModel();
		$this->view->assign('settings', $settings_model->getSettings());
		$this->view->assign('bill_fields', smBillHelper::getBillFields());
		$this->view->assign('bill_faximile_url', smBillHelper::getFaximileUrl());
    }
}