<?php

namespace App\Http\Controllers\Traits;

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
