<?php

class smFrontendMyProfileSaveImageController extends waJsonController
{
    public function execute()
    {
		if(!wa()->getUser()->isAuth()) {$this->response = array('result' => 0, 'message' => _w("You must log in")); return;}
		$image = waRequest::post('image', null);
		if(!is_array($image)) {$this->response = array('result' => 0, 'message' => _w("System error #NOARR")); return;}
		
		$contact_id = wa()->getUser()->getId();
		$contact_model = new waContactModel();
		$contact_data = $contact_model->getById($contact_id);
		if(!$contact_data) {$this->response = array('result' => 0, 'message' => _w("Contact not found")); return;}
		
		$filename = ifempty($image['path'], '');
		$x1 = intval(ifempty($image['x1'], 0));
		$x2 = intval(ifempty($image['x2'], 0));
		$y1 = intval(ifempty($image['y1'], 0));
		$y2 = intval(ifempty($image['y2'], 0));
		
		if($x1 >= $x2) {$this->response = array('result' => 0, 'message' => _w("Download error, the file may not be selected")); return;}
		if($y1 >= $y2) {$this->response = array('result' => 0, 'message' => _w("Download error, the file may not be selected")); return;}
		
		$path = wa()->getDataPath('photos', true, 'sm').'/'.$filename;
		if(!file_exists($path)) {$this->response = array('result' => 0, 'message' => _w("Image loading error")); return;}
		list($w, $h, $type) = getimagesize($path);
		$types = array("", "gif", "jpeg", "png");
		$ext = ifempty($types[$type], null);
		if(!$ext) {$this->response = array('result' => 0, 'message' => _w("Image loading error")); return;}
		$function = 'imagecreatefrom'.$ext;
		$input_image = $function($path);
        $width = $x2-$x1;
        $height = $y2-$y1;
        $output_image = imagecreatetruecolor($width, $height);
        imagecopy($output_image, $input_image, 0, 0, $x1, $y1, $x2, $y2);
        $function = 'image'.$ext;
        $function($output_image, $path);
		$file = waImage::factory($path);
		
		$contact_photo_path = wa()->getDataPath('contacts/images/', false, 'sm').$contact_id.'.'.$file->getExt();
		try {$file->resize(null, 200, 'HEIGHT', true)->crop(200, 200)->save($contact_photo_path);}
        catch(Exception $e)
		{
			waFiles::delete($path);
			$this->response = array('result' => 0, 'message' => _w("The avatar is not an image, or there was an error loading the file"));
			return;
		}
		
		$contact = wa()->getUser();
		$contact->setPhoto($contact_photo_path);
		waFiles::delete($contact_photo_path);
        waFiles::delete($path);
		
		$this->response = array('result' => 1, 'message' => _w("Data is saved"), 'photo_url' => $contact->getPhoto().'?v='.rand(0, 9999999));
    }
}