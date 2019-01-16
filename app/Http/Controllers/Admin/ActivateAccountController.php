<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;

class ActivateAccountController extends Controller
{
    /**
     * De-activate user.
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function deactivate(Request $request, User $user)
    {
        $user->deactivate();

        return $this->success('User has been de-activated.', $user);
    }

    /**
     * Re-activate user.
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function reactivate(Request $request, User $user)
    {
        $user->activate();

        return $this->success('User has been re-activated.', $user);
    }
}
