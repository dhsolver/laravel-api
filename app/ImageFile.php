<?php

namespace App;

use \Intervention\Image\Image as InterventionImage;

class ImageFile
{
    /**
     * Intervention image instance.
     *
     * @var \Intervention\Image\Image
     */
    private $image;

    public function __construct(InterventionImage $image)
    {
        $this->image = $image;
    }

    public function getRealPath()
    {
        return $this->image->basePath();
    }
}
