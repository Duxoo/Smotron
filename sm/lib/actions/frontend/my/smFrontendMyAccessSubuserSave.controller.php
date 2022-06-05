<?php

class smFrontendMyAccessSubuserSaveController extends waJsonController
{
    public function execute()
    {
        $data = waRequest::post('data', null);
        waLog::dump($data);
        $data['parent_contact_id'] = $this->getUserId();
        if(!is_array($data)) {$this->response = array('result' => 0, 'message' => _w("System error #NOARR")); return;}

        $user = new smUser(wa()->getUser()->getId());
        $user_data = $user->getData();
        $tariffs = smTariff::getUserTariffs($user_data['id']);
        $tariff_id = $user_data['tariff_id'];
        $tariff = null;
        if(isset($tariffs[$tariff_id])) {$tariff = $tariffs[$tariff_id];}

        if(!$user_data['subscribtion_valid'] && $tariff == null)
        {
            $this->response = array('result' => 0, 'message' => _w("The tariff is not enabled"));
            return;
        }

        if(!$user_data['subscribtion_valid'] && $tariff !== null)
        {
            $this->response = array('result' => 0, 'message' => _w("The tariff is not paid"));
            return;
        }

        $new = 1;
        $helper = new smHelper();
        $u = $helper->getSubuserByLogin($data['login']);
        $id = ifempty($data['id'], 0);
        unset($data['id']);
        $id = ($id == 0) ? $u['id'] : 0;

        $subuser = new smSubuser($id);
        $user_data['sub_user'] = $subuser->get();

        if ($user_data['sub_user']['parent_contact_id'] != $user_data['id']) {
            $this->response = array('result' => 0, 'message' => _w("Access error!"));
            return;
        }

        if($subuser->getId()) {
            $new = 0;
            $data['updatetime'] = date('Y-m-d  H:i:s');
        }
        else
        {
            $data['createdatetime'] = date('Y-m-d  H:i:s');
            $data['updatetime'] = date('Y-m-d  H:i:s');
        }
        if($data['password'] != '') { $data['password'] = $helper::getPasswordHash($data['password']); }else{ unset($data['password']); }
        $subuser->setAll($data);
        $subuser->save();

        $channels = array();
        $sub_user_id = $subuser->getId();
        $sub_user_channels_model = new smSubuserChannelsModel();
        $contactSubuserModel = new smSubuserModel();

        $sub_user_channels_model->deleteByField('subuser_id', $sub_user_id);
        if(isset($data['channels']))
        {
            foreach ($data['channels'] as $key => $value)
            {
                if($value == 'on')
                {
                    array_push($channels,array('subuser_id' => $sub_user_id, 'channel_id' => $key));
                }
            }
            $sub_user_channels_model->multipleInsert($channels);
        }
        if (isset($data['target_channel'])) {
            $contactSubuserModel->updateById($sub_user_id, array('target_channel' => $data['target_channel']));
        } else {
            $contactSubuserModel->updateById($sub_user_id, array('target_channel' => null));
        }
        $subuser->setAll($data);
        $subuser->save();
        $this->response = array('result' => 1, 'message' => _w("Data is saved"), 'id' => $subuser->getId(), 'new' => $new);
    }
}