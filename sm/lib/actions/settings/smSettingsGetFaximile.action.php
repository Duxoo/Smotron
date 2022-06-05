<?php
class smSettingsGetFaximileAction extends waViewAction
{
    public function execute()
    {
		$path = smBillHelper::getFaximilePath();
		if($path === null) {waFiles::readFile(wa()->getAppPath('img/no-faximile.png', 'sm'));}
		else {waFiles::readFile($path);}
    }
}
