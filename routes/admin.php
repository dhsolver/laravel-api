<?php
/*
|--------------------------------------------------------------------------
| CMS Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware(['jwt.auth', 'role:superadmin|admin'])->group(function () {
});
