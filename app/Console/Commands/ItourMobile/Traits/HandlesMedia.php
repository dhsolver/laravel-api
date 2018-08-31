<?php

namespace App\Console\Commands\ItourMobile\Traits;

use App\Http\Controllers\Traits\UploadsMedia;
use League\Flysystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Exception\NotSupportedException;
use Intervention\Image\Exception\NotReadableException;
use App\Exceptions\InvalidImageException;
use App\Exceptions\ImageTooSmallException;
use App\Media;

trait HandlesMedia
{
    use UploadsMedia;

    public function createMedia($type, $oldFilename, $user_id)
    {
        if (empty($oldFilename)) {
            return null;
        }

        if (substr($oldFilename, 0, 16) == '../API/tourfiles') {
            $oldFilename = substr($oldFilename, 7);
        }

        $file = config('junket.itourfiles') . '/' . trim($oldFilename);

        if (!file_exists($file)) {
            throw new FileNotFoundException($file);
            return;
        }

        if (($type == 'image' || $type == 'icon') && !in_array($this->getFileExtension($file), ['png', 'jpg', 'jpeg'])) {
            $file = $this->createTempFile($file);
        }

        $f = new UploadedFile($file, basename($file), mime_content_type($file));

        if ($type == 'image') {
            $filename = $this->storeImage($f, 'images', 'jpg', true);
        } elseif ($type == 'icon') {
            $filename = $this->storeIcon($f, 'images', 'png');
        } elseif ($type == 'audio') {
            $filename = $this->storeFile($f, 'audio', 'mp3');
        }

        $media = Media::create([
            'file' => $filename,
            'user_id' => $user_id,
        ]);

        return $media->id;
    }

    public function getObjRef()
    {
        if (empty($this->currentTour) && !empty($this->currentStop)) {
            return ', stop: ' . $this->currentStop->stop_id . (!empty($this->currentTour) ? ', tour: ' . $this->currentTour->tour_id : '');
        } elseif (!empty($this->currentTour) && empty($this->currentStop)) {
            return ', tour: ' . $this->currentTour->tour_id;
        }
    }

    public function createImage($obj, $field, $owner)
    {
        try {
            return $this->createMedia('image', $obj->$field, $owner);
        } catch (NotSupportedException $ex) {
            $this->log("Image type not supported (field: {$field}, value: {$obj->$field}" . $this->getObjRef() . ')');
            return false;
        } catch (NotReadableException $ex) {
            $this->log("Image not readable (field: {$field}, value: {$obj->$field}" . $this->getObjRef() . ')');
            return false;
        } catch (InvalidImageException $ex) {
            $this->log("Invalid image type (field: {$field}, value: {$obj->$field}" . $this->getObjRef() . ')');
            return false;
        } catch (ImageTooSmallException $ex) {
            $this->log("Image is too small (field: {$field}, value: {$obj->$field}" . $this->getObjRef() . ')');
            return false;
        } catch (FileNotFoundException $ex) {
            $this->log("Image file not found (field: {$field}, value: {$obj->$field}" . $this->getObjRef() . ')');
            return false;
        } catch (\Exception $ex) {
            $this->log("Unexpected image error (field: {$field}, value: {$obj->$field}" . $this->getObjRef() . '): ' . $ex->getMessage());
            return false;
        }
    }

    public function createAudio($obj, $field, $owner)
    {
        try {
            return $this->createMedia('audio', $obj->$field, $owner);
        } catch (NotSupportedException $ex) {
            $this->log("Audio type not supported (field: {$field}, value: {$obj->$field})" . $this->getObjRef() . ')');
            return false;
        } catch (NotReadableException $ex) {
            $this->log("Audio not readable (field: {$field}, value: {$obj->$field})" . $this->getObjRef() . ')');
            return false;
        } catch (InvalidImageException $ex) {
            $this->log("Invalid audio type (field: {$field}, value: {$obj->$field})" . $this->getObjRef() . ')');
            return false;
        } catch (FileNotFoundException $ex) {
            $this->log("Audio file not found (field: {$field}, value: {$obj->$field}" . $this->getObjRef() . ')');
            return false;
        } catch (\Exception $ex) {
            $this->log("Unexpected audio error (field: {$field}, value: {$obj->$field}" . $this->getObjRef() . '): ' . $ex->getMessage());
            return false;
        }
    }

    public function createIcon($obj, $owner)
    {
        if (empty($obj->icon)) {
            return null;
        }

        $filename = $obj->icon->url;

        // if the file doesn't exist try the active icon
        if (!$this->tourFileExists($filename) && !empty($obj->activeIcon)) {
            $filename = $obj->activeIcon->url;
        }

        try {
            return $this->createMedia('icon', $filename, $owner);
        } catch (NotSupportedException $ex) {
            $this->log("Icon type not supported (field: icon, value: {$filename}" . $this->getObjRef() . ')');
            return false;
        } catch (NotReadableException $ex) {
            $this->log("Icon not readable (field: icon, value: {$filename}" . $this->getObjRef() . ')');
            return false;
        } catch (InvalidImageException $ex) {
            $this->log("Invalid icon type (field: icon, value: {$filename}" . $this->getObjRef() . ')');
            return false;
        } catch (ImageTooSmallException $ex) {
            $this->log("Icon is too small (field: icon, value: {$filename}" . $this->getObjRef() . ')');
            return false;
        } catch (FileNotFoundException $ex) {
            $this->log("Icon file not found (field: icon, value: {$filename}" . $this->getObjRef() . ')');
            return false;
        } catch (\Exception $ex) {
            $this->log("Unexpected icon error (field: icon, value: {$filename}" . $this->getObjRef() . '): ' . $ex->getMessage());
            return false;
        }
    }

    public function createTempFile($file)
    {
        $contents = file_get_contents($file);

        $f = $this->generateFilename('jpg');

        if (\Storage::disk('local')->put('temp/' . $f, $contents)) {
            return \Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix() . 'temp/' . $f;
        }

        return null;
    }

    public function tourFileExists($filename)
    {
        if (empty($filename)) {
            return false;
        }

        return file_exists(config('junket.itourfiles') . '/' . $filename);
    }

    public function getFileExtension($file)
    {
        if (empty($file)) {
            return '';
        }

        try {
            $parts = pathinfo($file);
            return strtolower($parts['extension']);
        } catch (\Exception $ex) {
            return '';
        }
    }
}
