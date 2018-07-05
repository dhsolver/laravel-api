<?php
/*
|--------------------------------------------------------------------------
| CMS Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware(['jwt.auth', 'role:user|client|admin|superadmin'])->group(function () {
    Route::get('session', 'AuthController@userSession');
    Route::get('tours', 'TourController@index')->name('cms.tours.index');
    Route::get('tours/{tour}', 'TourController@show')->name('cms.tours.show')->middleware(['can:view,tour']);
    Route::resource('tours/{tour}/stops', 'StopController', ['as' => 'cms'])->middleware(['can:update,tour']);
});
