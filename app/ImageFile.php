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

    /**
     * ImageFile constructor.
     * @param InterventionImage $image
     */
    public function __construct(InterventionImage $image)
    {
        $this->image = $image;
    }

    /**
     * Get the image path.
     *
     * @return string
     */
    public function getRealPath()
    {
        return $this->image->basePath();
    }
}
