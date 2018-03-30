<?php
/*
|--------------------------------------------------------------------------
| CMS Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware(['jwt.auth', 'role:user|business|admin|superadmin'])->group(function () {
    Route::get('session', 'AuthController@userSession');
});
