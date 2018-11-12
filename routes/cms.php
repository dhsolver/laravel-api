<?php
/*
|--------------------------------------------------------------------------
| CMS API Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware(['jwt.auth', 'role:client|admin|superadmin'])->group(function () {
    Route::post('facebook/attach', 'AuthController@facebookAttach')->name('facebook.attach');
    Route::get('session', 'AuthController@userSession');
    Route::get('profile', 'AccountController@show');
    Route::patch('profile', 'AccountController@update');
    Route::patch('profile/password', 'AccountController@changePassword');

    Route::get('tours', 'TourController@index')->name('cms.tours.index');
    Route::post('tours', 'TourController@store')->name('cms.tours.store')->middleware(['can:create,App\Tour']);
    Route::get('tours/{tour}', 'TourController@show')->name('cms.tours.show')->middleware(['can:view,tour']);
    Route::put('tours/{tour}/stop-order', 'TourController@stopOrder')->name('cms.tour.order')->middleware(['can:update,tour']);
    Route::put('tours/{tour}/publish', 'TourController@publish')->name('cms.tours.publish')->middleware(['can:update,tour']);
    Route::put('tours/{tour}/unpublish', 'TourController@unpublish')->name('cms.tours.unpublish')->middleware(['can:update,tour']);
    Route::patch('tours/{tour}', 'TourController@update')->name('cms.tours.update')->middleware(['can:update,tour']);
    Route::delete('tours/{tour}', 'TourController@destroy')->name('cms.tours.destroy')->middleware(['can:delete,tour']);

    Route::resource('tours/{tour}/stops', 'StopController', ['as' => 'cms'])->middleware(['can:update,tour']);
    Route::put('tours/{tour}/stops/{stop}/order', 'StopController@changeOrder')->name('cms.stops.order')->middleware(['can:update,tour']);

    Route::post('media/upload', 'MediaController@store')->middleware(['can:create,App\Media'])->name('cms.media');
});
