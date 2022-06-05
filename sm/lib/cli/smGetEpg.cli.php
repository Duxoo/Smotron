<?php

class smGetEpgCli extends waCliController {

    public function execute() {
        $smAPI = new smFlussonicApi();
        $smAPI->downloadEpg();
        waLog::dump("done", "epg.log");
    }
}