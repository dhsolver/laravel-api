<?php
/*
|--------------------------------------------------------------------------
| CMS Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware(['jwt.auth', 'role:user|client|admin|superadmin'])->group(function () {
    Route::get('tours', 'TourController@index')->name('mobile.tours.index');
    Route::get('tours/{tour}', 'TourController@show')->name('mobile.tours.show');
});
