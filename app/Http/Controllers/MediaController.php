<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\UploadsMedia;
use App\Http\Requests\MediaUploadRequest;
use App\Media;

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
        if ($request->has('image')) {
            $filename = $this->storeFile($request->file('image'), 'images');
        } elseif ($request->has('audio')) {
            $filename = $this->storeFile($request->file('audio'), 'audio', 'mp3');
        } else {
            return $this->fail();
        }

        $media = Media::create([
            'file' => $filename,
            'user_id' => auth()->user()->id,
        ]);

        return $this->success('Media was uploaded successfully.', $media->toArray());
    }
}
