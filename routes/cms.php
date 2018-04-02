<?php
/*
|--------------------------------------------------------------------------
| CMS Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware(['jwt.auth', 'role:business|admin|superadmin'])->group(function () {
    Route::get('session', 'AuthController@userSession');
    Route::resource('tours', 'TourController', ['as' => 'cms']);
    Route::resource('tours/{tour}/stops', 'StopController', ['as' => 'cms']);
    Route::put('tours/{tour}/stops/{stop}', 'StopController@changeOrder')->name('cms.stops.order');
});
