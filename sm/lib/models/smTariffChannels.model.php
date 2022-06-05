<?php

class smTariffChannelsModel extends waModel
{
    protected $table = 'sm_tariff_channels';

    public function updateTariffChannels($id, $data)
    {
        if(!isset($id)) {return null;}
        $this->deleteByField('tariff_id',$id);
        $channels = array();
        $flag = 0;
        if(isset($data['is_custom'])){
            if($data['is_custom'] == 1){ $flag = 1; }
        }
        foreach ($data['channels'] as $key => $channel)
        {
            array_push($channels,array('tariff_id' => $id, 'tariff_type' => $flag,'channel_id' => $channel));
        }
        $this->multipleInsert($channels);
    }

    public function getChannelsByTariff($tariff_id)
    {
        $data = array(
            'tariff_id' => $tariff_id,
        );
        $channels = $this->getByField($data,true);
        $result = array();
        foreach ($channels as $key => $channel)
        {
            array_push($result,$channel['channel_id']);
        }
        //$result = array_flip($result);
        return $result;
    }

    public function getChannelsDataByTariff($tariff_id){
        $ids = $this->getChannelsByTariff($tariff_id);
        $channel = new smChannelModel();
        $query = "SELECT * FROM ".$this->table." as stc LEFT JOIN sm_channel as sc on stc.channel_id = sc.id WHERE stc.channel_id in (i:ids) AND sc.disabled = 0 AND tariff_id = i:t_id";
        /*return $channel->getById($ids);*/
        return $this->query($query, array('ids' => $ids, 't_id' => $tariff_id))->fetchAll();
    }
}