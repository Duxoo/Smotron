<?php

class smVideoModel extends waModel
{
    protected $table = 'sm_contact_videos';

    public function listEntities($start, $length, $order, $column, $direction, $search, $user_id)
    {
        $order_by = array(
            0 => 'uploadtime',
            1 => 'duration',
            2 => 'size',
            3 => 'name',
        );

        // Вычисление общего количества записей
        $rec_total = $this->query("SELECT COUNT(id) AS total FROM ".$this->table." WHERE contact_id = ".htmlspecialchars($user_id,ENT_QUOTES)." AND hidden = 0 ")->fetchAll();
        $rec_total = $rec_total[0]['total'];

        // Базовый запрос
        $query = "SELECT * FROM ".$this->table." WHERE  contact_id = ".htmlspecialchars($user_id,ENT_QUOTES)." AND hidden = 0";

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
        $videos = $this->query($query)->fetchAll();
        $user_filtered = $this->query($query_no_limit)->count();

        $result = array();
        $result['data'] = array();

        $flAPI = new smFlussonicApi();
        foreach($videos as $video)
        {
            $data = array(
                htmlspecialchars($video['name'], ENT_QUOTES),
                htmlspecialchars($video['duration'], ENT_QUOTES)
            );
            if ($video['status'] != "Готов") {
                $video_folder_path = smVideoHelper::getDir();
                $path_parts = pathinfo($video_folder_path.'/'.$user_id.'/'.$video['name']);
                $progress = $flAPI->getTranscodingProgress($user_id, $path_parts['filename']);
                if ($progress != NULL) {
                    $status = $video['status']."( {$progress} % )";
                    array_push($data, $status);
                } else {
                    array_push($data, $video['status']." (в очереди)");
                }
            } else {
                array_push($data, $video['status']);
            }
            array_push($data, '<i class="icon delete2" onclick="isDeleate('.$video['id'].')" class="dashboard ps-icon delete"></i>');
/*            array_push($result['data'], array(
                htmlspecialchars($video['name'], ENT_QUOTES),
                htmlspecialchars($video['duration'], ENT_QUOTES),
                htmlspecialchars($video['status'], ENT_QUOTES),
                '<i class="icon delete2" onclick="isDeleate('.$video['id'].')" class="dashboard ps-icon delete"></i>',
                //htmlspecialchars($video['status'], ENT_QUOTES),
            ));*/
            array_push($result['data'], $data);
        }

        $result['recordsTotal'] = $rec_total;
        if(!$result['recordsTotal']) {$result['recordsTotal'] = 0;}
        $result['recordsFiltered'] = $user_filtered;

        return $result;
    }

    public function getVideos($user_id)
    {
        $query = "SELECT * FROM ".$this->table." WHERE  contact_id = ".htmlspecialchars($user_id,ENT_QUOTES)." AND hidden = 0";
        $videos = $this->query($query)->fetchAll();
        return  $videos;
    }

    public function getVideosById($videos_id)
    {
        $unsort_videos_row = $this->getById($videos_id);

        $sort_videos = array();
        foreach ($videos_id as $video_id)
        {
            $sort_videos[] = $unsort_videos_row[$video_id];
        }
        return $sort_videos;
    }
}