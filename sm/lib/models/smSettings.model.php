<?php
class smSettingsModel extends waModel
{
    protected $table = 'sm_settings';
	protected $id = 'name';

	public function setParam($name, $value)
	{
		if($this->getById($name)) {$this->updateById($name, array('value' => $value));}
		else {$this->insert(array('name' => $name, 'value' => $value));}
	}
	
	public function getParam($name, $type = null, $default = null)
	{
		$param = $this->getById($name);
		if(!$param)
		{
			if($default !== null) {return $default;}
			return null;
		}
		
		$value = $param['value'];
		if($type == 'int') {$value = intval($value);}
		if($type == 'float') {$value = floatval($value);}
		return $value;
	}
	
	public function getSettings()
	{
		$params = $this->query("SELECT * FROM ".$this->table)->fetchAll('name');
		$result = array();
		if(count($params))
		{
			foreach($params as $key => $param)
			{
				$result[$key] = $param['value'];
			}
		}
		return $result;
	}

	public function getDiscountByMonthCount($count = 1)
    {
        switch ($count)
        {
            case 1:
                $discount = 0;
                break;
            case 3:
                $discount = $this->getParam('90day','int',0);
                break;
            case 6:
                $discount = $this->getParam('180day','int',0);
                break;
            default:
                $discount = 0;
                break;
        }
        return $discount;
    }
}
