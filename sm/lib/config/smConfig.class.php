<?php

class smConfig extends waAppConfig
{
    protected $image_sizes = array(
        'big'        => '970',
        'default'    => '750x0',
        'thumb'      => '200x0',
        'crop'       => '96x96',
        'crop_small' => '48x48'
    );

    public function getImageSize($name)
    {
        return isset($this->image_sizes[$name]) ? $this->image_sizes[$name] : null;
    }

    public function getImageSizes($type = 'all')
    {
        if ($type == 'system') {
            return $this->image_sizes;
        }
        $custom_sizes = $this->getOption('image_sizes');
        if ($type == 'custom') {
            return $custom_sizes;
        }
        $sizes = array_merge(array_values($this->image_sizes), array_values($custom_sizes));
        return array_unique($sizes);
    }
    
}