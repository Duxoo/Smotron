<?php

class smRestApiController extends waJsonController
{
    public function execute()
    {
        $data = waRequest::get();
        $api = new smRestApi();

        $this->response = $api->processCommand($data);
    }
}