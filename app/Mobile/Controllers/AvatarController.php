<?php

namespace App\Mobile\Controllers;

use App\Http\Controllers\Traits\UploadsMedia;
use App\Exceptions\ImageTooSmallException;
use App\Exceptions\InvalidImageException;
use App\Http\Controllers\Controller;
use App\Mobile\Resources\ProfileResource;
use \Illuminate\Http\Request;

class AvatarController extends Controller
{
    use UploadsMedia;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            if ($request->hasFile('image')) {
                $filename = $this->storeAvatar($request->file('image'));
            } else {
                return $this->fail(422, 'No image found in request');
            }

            auth()->user()->update(['avatar' => $filename]);

            return $this->success('Avatar was uploaded successfully.', new ProfileResource(auth()->user()->fresh()));
        } catch (ImageTooSmallException $ex) {
            return $this->fail(422, $ex->getMessage());
        } catch (InvalidImageException $ex) {
            return $this->fail(422, $ex->getMessage());
        } catch (\Exception $ex) {
            return $this->fail();
        }
    }
}
