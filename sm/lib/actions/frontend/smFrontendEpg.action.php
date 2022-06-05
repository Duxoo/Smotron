<?php

class smFrontendEpgAction extends waViewAction
{
    public function execute()
    {
        $channel_fl_id = waRequest::get('channel');
        $token = waRequest::get('token');
        $day = waRequest::get('day');

        $this->setThemeTemplate('epg.html');

        $channelModel = new smChannelModel();
        $channelInfo = $channelModel->getByField('fl_channel_name',$channel_fl_id);

        if($day == 1){ $time = date('Y-m-d H:i:s',time()); }
        else{ $time = date('Y-m-d H:i:s',strtotime("+1 day")); }

        $date_array = date_parse_from_format('Y-m-d H:i:s', $time);
        $api = new smFlussonicApi();
        $epg = $api->getEpg($channelInfo['epg_id'],$date_array); //500
        $result = array();

        if(!isset($epg)||empty($epg)){$this->view->assign('epg',null); return;}
        
        foreach ($epg as $key => $value)
        {
            //if(($time < $value['end'])&&($value['end'] < $tomorrow)){
                $epg[$key]['start'] = date('H:i',$value['start']);
                //array_push($result, $value);
            //};
        }
        $this->view->assign('epg',$epg);
        return;
        //todo разобраться почему epg приходит не за ту дату
    }
}