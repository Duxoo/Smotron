<?php

class smVideoHelper
{
    static public function getDir()
    {
        return wa()->getDataPath('videos', true, 'sm');
    }
}