<?php
/*
|--------------------------------------------------------------------------
| CMS Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware(['jwt.auth', 'role:client|admin|superadmin'])->group(function () {
    Route::get('session', 'AuthController@userSession');
    Route::resource('tours', 'TourController', ['as' => 'cms']);
    Route::resource('tours/{tour}/stops', 'StopController', ['as' => 'cms']);
    Route::put('tours/{tour}/stops/{stop}/order', 'StopController@changeOrder')->name('cms.stops.order');
    Route::put('tours/{tour}/stops/{stop}', 'StopController@uploadMedia')->name('cms.stops.media');
    Route::put('tours/{tour}/media', 'TourController@uploadMedia')->name('cms.tours.media');
});
