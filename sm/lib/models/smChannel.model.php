<?php

class smChannelModel extends waModel
{
    protected $table = 'sm_channel';

    public function listEntities($start, $length, $order, $column, $direction, $search)
    {
        $order_by = array(
            0 => 'id',
            1 => 'image',
            2 => 'name',
            3 => 'fl_channel_name',
            4 => 'disabled',
        );

        // Вычисление общего количества записей
        $rec_total = $this->query("SELECT COUNT(id) AS total FROM ".$this->table." WHERE hidden = 0 ")->fetchAll();
        $rec_total = $rec_total[0]['total'];

        // Базовый запрос
        $query = "SELECT * FROM ".$this->table." WHERE hidden = 0";

        // Поиск
        if($search)
        {
            $query = $query." AND (id = ".intval($search)." OR name LIKE ('%".$this->escape($search)."%'))";
        }

        // Сортировка
        if($order)
        {
            if(isset($order_by[$column])) {$query .= " ORDER BY ".$order_by[$column]." ".$direction;}
            else {$query .= " ORDER BY ".$order_by[0]." ".$direction;}
        }
        $query_no_limit = $query;

        // Количество
        $query.=" LIMIT ".intval($start).",".intval($length);

        // Выполнение поиска
        $channels = $this->query($query)->fetchAll();
        $user_filtered = $this->query($query_no_limit)->count();

        $result = array();
        $result['data'] = array();



        if($user_filtered > 0)
        {
            foreach($channels as $channel)
            {
                if($channel['disabled'] != 0 )
                {
                    $check = 'Закрыт для трансляции';
                }else{
                    $check = 'Доступен для трансляции';
                }
                if(!isset($channel['image'])){$channel['image'] = wa()->getAppStaticUrl('sm').'img/dummy.jpg';}
                array_push($result['data'], array(
                    $channel['id'],
                    '<a href="?module=channel&id='.$channel['id'].'"><img class="channel-img" src="'.$channel['image'].'" tittle="'.htmlspecialchars($channel['name'], ENT_QUOTES).'" alt="'.htmlspecialchars($channel['name'], ENT_QUOTES).'"></a>',
                    '<a href="?module=channel&id='.$channel['id'].'">'.htmlspecialchars($channel['name'], ENT_QUOTES).'</a>',
                    '<a href="?module=channel&id='.$channel['id'].'">'.htmlspecialchars($channel['price'], ENT_QUOTES).'</a>',
                    $check,
                    '<a href="?module=channel&id='.$channel['id'].'">'.htmlspecialchars($channel['fl_channel_name'], ENT_QUOTES).'</a>',
                ));
            }
        }

        $result['recordsTotal'] = $rec_total;
        if(!$result['recordsTotal']) {$result['recordsTotal'] = 0;}
        $result['recordsFiltered'] = $user_filtered;

        return $result;
    }

    public function listCustom($start, $length, $order, $column, $direction, $search, $user_id)
    {
        $user = new smUser($user_id);
        $user_data = $user->getData();
        $order_by = array(
            0 => 'id',
            1 => 'image',
            2 => 'name',
            3 => 'fl_channel_name',
            4 => 'disabled',
        );

        // Вычисление общего количества записей
        $rec_total = $this->query("SELECT COUNT(id) AS total FROM ".$this->table." WHERE hidden = 0 AND is_custom = 1 AND contact_id = ".$user_id)->fetchAll();
        $rec_total = $rec_total[0]['total'];

        // Базовый запрос
        $query = "SELECT * FROM ".$this->table." WHERE hidden = 0 AND is_custom = 1 AND contact_id = ".$user_id;

        // Поиск
        if($search)
        {
            $query = $query." AND (id = ".intval($search)." OR name LIKE ('%".$this->escape($search)."%'))";
        }

        // Сортировка
        if($order)
        {
            if(isset($order_by[$column])) {$query .= " ORDER BY ".$order_by[$column]." ".$direction;}
            else {$query .= " ORDER BY ".$order_by[0]." ".$direction;}
        }
        $query_no_limit = $query;

        // Количество
        $query.=" LIMIT ".intval($start).",".intval($length);

        // Выполнение поиска
        $channels = $this->query($query)->fetchAll();
        $user_filtered = $this->query($query_no_limit)->count();

        $result = array();
        $result['data'] = array();



        if($user_filtered > 0)
        {
            foreach($channels as $channel)
            {
                if($user_data['premoderation'] == 'on')
                {
                    if($channel['premoderation_flag'] == 'off')
                    {
                        if($channel['disabled'] != 0 )
                        {
                            $check = 'Закрыт для трансляции';
                        }
                        else
                        {
                            $check = 'В процессе модерации';
                        }
                    }
                    else
                    {
                        if($channel['disabled'] != 0 )
                        {
                            $check = 'Закрыт для трансляции';
                        }
                        else
                        {
                            $check = 'Доступен для трансляции';
                        }
                    }
                }
                else
                {
                    if($channel['disabled'] != 0 )
                    {
                        $check = 'Закрыт для трансляции';
                    }
                    else
                    {
                        $check = 'Доступен для трансляции';
                    }
                }

                if(!isset($channel['image'])){$channel['image'] = wa()->getAppStaticUrl('sm').'img/dummy.jpg';}
                array_push($result['data'], array(
                    '<span data-name="'.$channel["fl_channel_name"].'">'.htmlspecialchars($channel['name'], ENT_QUOTES).'</span>',
                    $check,
                    '<a class="icon edit dashboard ps-icon edit" href="edit/?id='.$channel['id'].'"></a>',
                    '<i class="icon delete2" onclick="chDeleate('.$channel['id'].')" class="dashboard ps-icon delete"></i>',
                ));
            }
        }

        $result['recordsTotal'] = $rec_total;
        if(!$result['recordsTotal']) {$result['recordsTotal'] = 0;}
        $result['recordsFiltered'] = $user_filtered;

        return $result;
    }

    public function backendListCustom($start, $length, $order, $column, $direction, $search, $user_id)
    {
        $settings = new smSettingsModel();
        $order_by = array(
            0 => 'id',
            1 => 'image',
            2 => 'name',
            3 => 'fl_channel_name',
            4 => 'disabled',
        );

        // Вычисление общего количества записей
        $rec_total = $this->query("SELECT COUNT(id) AS total FROM ".$this->table." WHERE hidden = 0 AND is_custom = 1 AND contact_id = ".$user_id)->fetchAll();
        $rec_total = $rec_total[0]['total'];

        // Базовый запрос
        $query = "SELECT * FROM ".$this->table." WHERE hidden = 0 AND is_custom = 1 AND contact_id = ".$user_id;

        // Поиск
        if($search)
        {
            $query = $query." AND (id = ".intval($search)." OR name LIKE ('%".$this->escape($search)."%'))";
        }

        // Сортировка
        if($order)
        {
            if(isset($order_by[$column])) {$query .= " ORDER BY ".$order_by[$column]." ".$direction;}
            else {$query .= " ORDER BY ".$order_by[0]." ".$direction;}
        }
        $query_no_limit = $query;

        // Количество
        $query.=" LIMIT ".intval($start).",".intval($length);

        // Выполнение поиска
        $channels = $this->query($query)->fetchAll();
        $user_filtered = $this->query($query_no_limit)->count();

        $result = array();
        $result['data'] = array();



        if($user_filtered > 0)
        {
            foreach($channels as $channel)
            {
                if($channel['disabled'] != 0 )
                {
                    $check = 'Закрыт для трансляции';
                }else{
                    $check = 'Доступен для трансляции';
                }

                if($channel['premoderation_flag'] != "off")
                {
                    $premoderation = '<i id="delte-button" onclick="isCheck('.htmlspecialchars($channel['id'],ENT_QUOTES).')" class="check sm-icon checkbox-custom-active"></i>';
                }else{
                    $premoderation = '<i id="delte-button" onclick="isCheck('.htmlspecialchars($channel['id'],ENT_QUOTES).')" class="check sm-icon checkbox-custom"></i>';
                }
waLog::dump($channel);
                if(!isset($channel['image'])){$channel['image'] = wa()->getAppStaticUrl('sm').'img/dummy.jpg';}
                array_push($result['data'], array(
                    $premoderation,
                    '<span data-name="'.$channel["fl_channel_name"].'">'.htmlspecialchars($channel['name'], ENT_QUOTES).'</span>',
                    $check,
                    //'<i class="icon delete2" onclick="chDeleate('.$channel['id'].')" class="dashboard ps-icon delete"></i>',
                    /*smFlussonicApi::test($channel["fl_channel_name"], $settings->getParam('flussonic_token'))*/
                    '<a href="?module=clients&action=editCustomChannel&channel_id='.$channel['id'].'&user_id='.$user_id.'">Редактировать</a>',

                ));
            }
        }

        $result['recordsTotal'] = $rec_total;
        if(!$result['recordsTotal']) {$result['recordsTotal'] = 0;}
        $result['recordsFiltered'] = $user_filtered;

        return $result;
    }

    public function getChannels(){
        $query = "SELECT id, name FROM ".$this->table." WHERE hidden = 0";
        $result = $this->query($query)->fetchAll();
        return $result;
    }

    public function getEnabledChannel()
    {
        $query = "SELECT * FROM ".$this->table." WHERE hidden = 0 AND disabled = 0 AND is_custom = 0";
        return $this->query($query)->fetchAll();
    }

    public function getChannelsById($id)
    {
        if(!isset($id)){return $this->getEnabledChannel();}
        return $this->getById($id);
    }

    public function createCustomChannel($name, $contact_id)
    {
        $user = new smUser($contact_id);
        $tariff = new smTariff($user->getData('tariff_id'));
        $count = $tariff->getData('channel_custom_count');
        $user_custom_count = count($this->getByField(array(
            'is_custom' => 1,
            'contact_id' => $contact_id,
        )));
        if($user_custom_count + 1 > $count){return array('result'=> 0, 'message' => 'Ваш тариф не позволяет создать больше каналов.');}
        /*$name = $name.$contact_id;*/
        $time = time();
        $hash = hash("md5", $name.$time);
        $data = array(
            'name' => $name,
            'fl_channel_name' => $hash,
            'is_custom' => 1,
            'disabled' => 1,
            'contact_id' => $contact_id,
            'image' => '/wa-apps/sm/img/dummy.jpg',
        );
        try{
            waLog::dump($data);
            $this->insert($data);
            $row = $this->getByField($data);
            $this->updateById($row['id'],array('fl_channel_name' => hash("md5",$name.$time)));
        }catch (waException $e)
        {
            $this->response = array('result' => 0, 'message' => $e->getMessage());
            return;
        }
        return array('result' => 1, 'message' => 'Канал добавлен', 'contact_id' => $contact_id, 'row_id' => $row['id'], 'time' => $time);
    }

    public function deleteChannelById($id, $contact_id)
    {
        $row = $this->query("SELECT * FROM ".$this->table." WHERE id = i:id AND is_custom = 1 AND contact_id = i:cid",array('id' => intval($id), 'cid' => intval($contact_id)));
        if(!isset($row)){return array('result' => 0, 'message' => 'ОШИБКА: Канал не найден.');}
        $this->deleteById($id);
        return array('result' => 1, 'message' => 'Канал удален.');
    }

    public function updateChannelStatus($video_name, $contact_id)
    {
        $this->updateByField(array('fl_channel_name' => $video_name, 'contact_id' => $contact_id),array('disabled' => 0));
    }

    public function getChannelsByUser($user_id)
    {
        $query = "SELECT * FROM ".$this->table." WHERE hidden = 0 AND is_custom = 1 AND contact_id = ".$user_id;
        return $this->query($query)->fetchAll();
    }
}