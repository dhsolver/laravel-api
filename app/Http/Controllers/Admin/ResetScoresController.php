<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;

class ResetScoresController extends Controller
{    
    /**
     * Reset user's scores.
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function reset(Request $request, User $user)
    {
        $user->reset();
        return $this->success('Successfully reset.', $user);
    }
}
