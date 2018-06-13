<?php

namespace App\Http\Controllers\Traits;

use App\ImageFile;
use App\Exceptions\ImageTooSmallException;

trait UploadsMedia
{
    /**
     * Image mime types that are allowed.
     *
     * @var array
     */
    protected $imageMimes = ['image/gif', 'image/jpg', 'image/jpeg', 'image/png'];

    /**
     * Video mime types that are allowed.
     *
     * @var array
     */
    protected $videoMimes = ['video/avi', 'video/mpeg', 'video/quicktime', 'video/mp4'];

    /**
     * Generates unique hash based filename.
     *
     * @param string $ext
     * @return void
     */
    public function generateFilename($ext = 'jpg')
    {
        return md5(uniqid() . microtime()) . '.' . $ext;
    }

    /**
     * Add string to the end of the filename.
     *
     * @param string $filename
     * @param string $mod
     * @return string
     */
    public function modFilename($filename, $mod)
    {
        return substr($filename, 0, strpos($filename, '.')) . $mod . substr($filename, strpos($filename, '.'));
    }

    /**
     * Store the uploaded file to the proper S3 location.
     *
     * @param [type] $file
     * @param [type] $dir
     * @return void
     */
    public function storeFile($file, $dir, $ext = null)
    {
        $filename = $this->generateFilename($ext ? $ext : $file->extension());

        if (!\Storage::putFileAs($dir, $file, $filename)) {
            // error saving image -> quit
            return false;
        }

        return $dir . '/' . $filename;
    }

    /**
     * Process the given image and store all versions.
     *
     * @param [type] $file
     * @param [type] $dir
     * @return void
     */
    public function storeImage($file, $dir, $ext = null)
    {
        $thumbSize = config('junket.imaging.image_thumb_size', 400);
        $maxSize = config('junket.imaging.max_image_size', 3000);

        $filename = $this->generateFilename($ext ? $ext : $file->extension());

        $image = \Image::make($file->path());

        if ($image->height() < $thumbSize || $image->width() < $thumbSize) {
            throw new ImageTooSmallException("Image too small.  Images must be at least {$thumbSize}x{$thumbSize}.");
        }

        // resize original if exceeds max
        if ($image->height() > $maxSize || $image->width() > $maxSize) {
            if ($image->height() < $image->width()) {
                $image->resize(null, $maxSize, function ($constraint) {
                    $constraint->aspectRatio();
                })->save();
            } else {
                $image->resize($maxSize, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save();
            }
        }

        if (!\Storage::putFileAs($dir, new ImageFile($image), $filename)) {
            // error saving image -> quit
            return false;
        }

        // create smaller version
        $image = \Image::make($file->path());

        if ($image->height() < $image->width()) {
            $image->resize(null, $thumbSize, function ($constraint) {
                $constraint->aspectRatio();
            })->save();
        } else {
            $image->resize($thumbSize, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save();
        }

        if (!\Storage::putFileAs($dir, new ImageFile($image), $this->modFilename($filename, '_sm'))) {
            // error saving image -> quit
            return false;
        }

        return $dir . '/' . $filename;
    }

    /**
     * Process the given icon image and store all versions.
     *
     * @param [type] $file
     * @param [type] $dir
     * @return void
     */
    public function storeIcon($file, $dir, $ext = null)
    {
        $thumbSize = config('junket.imaging.icon_size', 48);
        $maxSize = config('junket.imaging.max_icon_size', 3000);

        $filename = $this->generateFilename($ext ? $ext : $file->extension());

        $image = \Image::make($file);

        if ($image->height() < $thumbSize || $image->width() < $thumbSize) {
            throw new ImageTooSmallException("Image too small.  Images must be at least {$thumbSize}x{$thumbSize}.");
        }

        // resize original if exceeds max
        if ($image->height() > $maxSize || $image->width() > $maxSize) {
            if ($image->height() < $image->width()) {
                $image->resize(null, $maxSize, function ($constraint) {
                    $constraint->aspectRatio();
                })->save();
            } else {
                $image->resize($maxSize, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save();
            }
        }

        // force into square
        if ($image->height() > $image->width()) {
            $image->fit($image->width(), $image->width())->save();
        } else {
            $image->fit($image->height(), $image->height())->save();
        }

        if (!\Storage::putFileAs($dir, new ImageFile($image), $filename)) {
            // error saving image -> quit
            return false;
        }

        $image->fit($thumbSize, $thumbSize)->save();

        if (!\Storage::putFileAs($dir, new ImageFile($image), $this->modFilename($filename, '_ico'))) {
            // error saving image -> quit
            return false;
        }

        return $dir . '/' . $filename;
    }

    /**
     * Validates file mime type as an image.
     *
     * @param [type] $file
     * @return boolean
     */
    public function isValidImageFile($file)
    {
        return in_array(mime_content_type($file->path()), $this->imageMimes);
    }

    /**
     * Validates file mime type as a video.
     *
     * @param [type] $file
     * @return boolean
     */
    public function isValidVideoFile($file)
    {
        return in_array(mime_content_type($file->path()), $this->videoMimes);
    }
}
