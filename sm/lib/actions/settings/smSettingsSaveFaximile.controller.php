<?php
class smSettingsSaveFaximileController extends waJsonController
{
    public function execute()
    {
		$file = waRequest::file('file');
		if(!$file->uploaded()) {$this->response = array('result' => 0, 'message' => 'Ошибка загрузки файла'); return;}
		$settings_model = new smSettingsModel();
		$faximile_file_name = $settings_model->getParam('faximile_file_name', 'string', null);
		try {
			$path = smBillHelper::getFaximileDir();
			$image = $file->waImage();
			if(file_exists($path.'/'.$faximile_file_name) && $faximile_file_name) {waFiles::delete($path.'/'.$faximile_file_name);}
			$filename = 'faximile.'.$file->extension;
			$image->save($path.'/'.$filename);
			$settings_model->setParam('faximile_file_name', $filename);
		}
		catch (Exception $e) {
			$this->response = array('result' => 0, 'message' => 'Ошибка загрузки файла');
			return;
		}
		
		$this->response = array('result' => 1, 'message' => 'Печать загружена', 'url' => '?module=settings&action=getFaximile');
    }
}
