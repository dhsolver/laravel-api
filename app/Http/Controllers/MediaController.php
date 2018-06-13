<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\UploadsMedia;
use App\Http\Requests\MediaUploadRequest;
use App\Media;
use App\Exceptions\ImageTooSmallException;

class MediaController extends Controller
{
    use UploadsMedia;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MediaUploadRequest $request)
    {
        try {
            if ($request->has('image')) {
                $filename = $this->storeImage($request->file('image'), 'images', 'jpg');
            } elseif ($request->has('icon')) {
                $filename = $this->storeIcon($request->file('icon'), 'images', 'png');
            } elseif ($request->has('audio')) {
                $filename = $this->storeFile($request->file('audio'), 'audio', 'mp3');
            } else {
                return $this->fail();
            }
        } catch (ImageTooSmallException $ex) {
            return $this->fail(422, $ex->message);
        }

        $media = Media::create([
            'file' => $filename,
            'user_id' => auth()->user()->id,
        ]);

        return $this->success('Media was uploaded successfully.', $media->toArray());
    }
}
