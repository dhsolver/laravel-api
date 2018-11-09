<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\UploadsMedia;
use App\Http\Requests\MediaUploadRequest;
use App\Media;
use App\Exceptions\ImageTooSmallException;
use App\Exceptions\InvalidImageException;
use App\Exceptions\InvalidAudioFileException;
use App\Audio\AudioProcessor;

class MediaController extends Controller
{
    use UploadsMedia;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MediaUploadRequest $request, AudioProcessor $audio)
    {
        $media = null;
        try {
            if ($request->has('image')) {
                $filename = $this->storeImage($request->file('image'), 'images', 'jpg');
                $media = auth()->user()->media()->create(['type' => Media::TYPE_IMAGE, 'file' => $filename]);
            } elseif ($request->has('icon')) {
                $filename = $this->storeIcon($request->file('icon'), 'images', 'png');
                $media = auth()->user()->media()->create(['type' => Media::TYPE_ICON, 'file' => $filename]);
            } elseif ($request->has('audio')) {
                $length = $audio->getDuration($request->file('audio'));
                $filename = $this->storeFile($request->file('audio'), 'audio', 'mp3');
                $media = auth()->user()->media()->create([
                    'type' => Media::TYPE_AUDIO,
                    'file' => $filename,
                    'length' => $length
                ]);
            } else {
                return $this->fail();
            }

            return $this->success('Media was uploaded successfully.', $media->toArray());
        } catch (ImageTooSmallException $ex) {
            return $this->fail(422, $ex->message);
        } catch (InvalidImageException $ex) {
            return $this->fail(422, $ex->message);
        } catch (InvalidAudioFileException $ex) {
            return $this->fail(422, $ex->message);
        }

        return $this->fail();
    }
}
