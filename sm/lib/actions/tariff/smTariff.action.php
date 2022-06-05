<?php

class smTariffAction extends waViewAction
{
    public function execute()
    {
        $this->setLayout(new smBackendLayout());
        $id = waRequest::get('id', 0, 'int');
        $tariff = new smTariff($id);
        $channel = new smChannelModel();

        $data_tariff = $tariff->get();
        $channels = $channel->getEnabledChannel();
        //$all_channel = $channel->getAll('id');
        foreach($channels as $key => $channel)
        {
            if(isset($data_tariff['channels']))
            {
                foreach($data_tariff['channels'] as $t_key => $t_channel)
                {
                    if($channel['id'] == $t_channel)
                    {
                        $channels[$key]['checked'] = true;
                    }
                }
            }
        }

        $this->view->assign('channels', $channels);
        $this->view->assign('data', $data_tariff);
        $this->view->assign('id', $tariff->getId());
    }
}