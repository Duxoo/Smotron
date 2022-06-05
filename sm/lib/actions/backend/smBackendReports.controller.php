<?php
class smBackendReportsController extends waViewController
{
    public function execute()
    {
        $this->setLayout(new smBackendLayout());
        $this->executeAction(new smBackendReportsAction());

    }
}
