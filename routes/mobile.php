<?php
/*
|--------------------------------------------------------------------------
| CMS Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware(['jwt.auth', 'role:user|client|admin|superadmin'])->group(function () {
    Route::get('session', 'AuthController@userSession');
});
