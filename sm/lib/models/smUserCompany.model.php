<?php
class smUserCompanyModel extends waModel
{
    protected $table = 'sm_user_company';
	
	public function setData($user_id, $data)
	{
		if(isset($data['id'])) {unset($data['id']);}
		if($this->getById($user_id))
		{
			$this->updateById($user_id, $data);
		}
		else
		{
			$data['id'] = $user_id;
			$this->insert($data);
		}
	}
}
