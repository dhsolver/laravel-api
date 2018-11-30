<?php

namespace App\Http\Controllers\Traits;

use App\ImageFile;
use App\Exceptions\ImageTooSmallException;
use App\Exceptions\InvalidImageException;
use Intervention\Image\Exception\NotReadableException;

trait UploadsMedia
{
    /**
     * Image mime types that are allowed.
     *
     * @var array
     */
    protected $imageMimes = ['image/gif', 'image/jpg', 'image/jpeg', 'image/png'];

    /**
     * Icon mime types that are allowed.
     *
     * @var array
     */
    protected $iconMimes = ['image/gif', 'image/jpg', 'image/jpeg', 'image/png'];

    /**
     * Video mime types that are allowed.
     *
     * @var array
     */
    protected $videoMimes = ['video/avi', 'video/mpeg', 'video/quicktime', 'video/mp4'];

    /**
     * Audio mime types that are allowed.
     *
     * @var array
     */
    protected $audioMimes = ['audio/mpeg'];

    /**
     * Generates unique hash based filename.
     *
     * @param string $ext
     * @return string
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
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $dir
     * @param string $ext
     * @return bool|string
     * @throws \App\Exceptions\InvalidImageException
     */
    public function storeFile($file, $dir, $ext = null)
    {
        $this->validateMime($file->path(), $this->audioMimes);

        $filename = $this->generateFilename($ext ? $ext : $file->extension());

        if (! \Storage::putFileAs($dir, $file, $filename)) {
            // error saving image -> quit
            return false;
        }

        return $dir . '/' . $filename;
    }

    /**
     * Process the given image and store all versions.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $dir
     * @param string $ext
     * @param bool $sizeUp
     * @return string|bool
     * @throws \App\Exceptions\InvalidImageException
     * @throws \App\Exceptions\ImageTooSmallException
     */
    public function storeImage($file, $dir, $ext = null, $sizeUp = false)
    {
        $thumbSize = config('junket.imaging.image_thumb_size', 400);
        $maxSize = config('junket.imaging.max_image_size', 3000);

        $filename = $this->generateFilename($ext ? $ext : $file->extension());

        $image = \Image::make($file->path());
        $this->validateMime($image, $this->imageMimes);

        if ($sizeUp && ($image->height() < $thumbSize || $image->width() < $thumbSize)) {
            if ($image->height() < $image->width()) {
                $image->resize(null, $thumbSize, function ($constraint) {
                    $constraint->aspectRatio();
                })->save();
            } else {
                $image->resize($thumbSize, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save();
            }
        } elseif ($image->height() < $thumbSize || $image->width() < $thumbSize) {
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

        if (! \Storage::putFileAs($dir, new ImageFile($image), $filename)) {
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

        if (! \Storage::putFileAs($dir, new ImageFile($image), $this->modFilename($filename, '_sm'))) {
            // error saving image -> quit
            return false;
        }

        return $dir . '/' . $filename;
    }

    /**
     * Process the given icon image and store all versions.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $dir
     * @param string $ext
     * @return string|bool
     * @throws \App\Exceptions\InvalidImageException
     * @throws \App\Exceptions\ImageTooSmallException
     */
    public function storeIcon($file, $dir, $ext = null)
    {
        $thumbSize = config('junket.imaging.icon_size', 164);
        $maxSize = config('junket.imaging.max_icon_size', 3000);

        $filename = $this->generateFilename($ext ? $ext : $file->extension());

        try {
            $image = \Image::make($file);
        } catch (NotReadableException $ex) {
            throw new InvalidImageException('File type not supported.');
        }
        $this->validateMime($image, $this->iconMimes);

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

        if (! \Storage::putFileAs($dir, new ImageFile($image), $filename)) {
            // error saving image -> quit
            return false;
        }

        $image->fit($thumbSize, $thumbSize)->save();

        if (! \Storage::putFileAs($dir, new ImageFile($image), $this->modFilename($filename, '_ico'))) {
            // error saving image -> quit
            return false;
        }

        return $dir . '/' . $filename;
    }

    /**
     * Process the given avatar image and store.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $ext
     * @return string|bool
     * @throws \App\Exceptions\InvalidImageException
     */
    public function storeAvatar($file, $ext = null)
    {
        $avatarSize = config('junket.imaging.avatar_size', 750);

        $filename = $this->generateFilename($ext ? $ext : $file->extension());

        try {
            $image = \Image::make($file);
        } catch (NotReadableException $ex) {
            throw new InvalidImageException('File type not supported.');
        }
        $this->validateMime($image, $this->imageMimes);

        // force into square
        $image->fit($avatarSize, $avatarSize)->save();

        if (! \Storage::putFileAs('avatars', new ImageFile($image), $filename)) {
            // error saving image -> quit
            return false;
        }

        return $filename;
    }

    /**
     * Throws an error if Image's mime type is not in the given array.
     * Accepts either an object of Image class or a string filename.
     *
     * @param string|Intervention\Image\Image $imageOrFilename
     * @return bool
     * @param array $mimes
     * @throws \App\Exceptions\InvalidImageException
     */
    public function validateMime($imageOrFilename, $mimes)
    {
        if (is_a($imageOrFilename, 'Intervention\Image\Image')) {
            if (! in_array($imageOrFilename->mime(), $mimes)) {
                throw new InvalidImageException('File type not supported.');
            }
        } else {
            if (! in_array(mime_content_type($imageOrFilename), $mimes)) {
                throw new InvalidImageException('File type not supported.');
            }
        }

        return true;
    }
}
