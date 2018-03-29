<?php
/*
|--------------------------------------------------------------------------
| CMS Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware(['jwt.auth', 'role:business|admin'])->group(function () {
    Route::resource('tours', 'TourController');
});
