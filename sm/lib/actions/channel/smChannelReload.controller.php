<?php

class smChannelReloadController extends waJsonController
{
    public function execute()
    {
        $model = new smChannelModel();
        $channels = $model->getAll('fl_channel_name');
        $helper = new smHelper();
        $new_channels = $helper->getAllChannels();
        $tmp = array();
        foreach ($new_channels as $channel)
        {
            //waLog::dump($channel);
            if(isset($channel['value']['name']))
            {
                if(!isset($channels[$channel['value']['name']]))
                {
                        array_push($tmp,array('fl_channel_name' => $channel['value']['name']));
                }
            }
        }
        try
        {
            $model->multipleInsert($tmp);
            unset($tmp);
            $this->response = array('result' => 1, 'message' => 'Список обновлен');
            return;
        }
        catch(waException $e)
        {
            $this->response = array('result' => 0, 'message' => $e->getMessage());
            return;
        }
    }
}