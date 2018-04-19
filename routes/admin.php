<?php
/*
|--------------------------------------------------------------------------
| CMS Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware(['jwt.auth', 'role:superadmin|admin'])->group(function () {
    Route::get('session', 'AuthController@userSession');

    Route::get('clients', 'Admin\ClientController@index')->name('admin.clients.index');
    Route::post('clients', 'Admin\ClientController@store')->name('admin.clients.store');
    Route::get('clients/{client}', 'Admin\ClientController@show')->name('admin.clients.show');
    Route::patch('clients/{client}', 'Admin\ClientController@update')->name('admin.clients.update');
    Route::delete('clients/{client}', 'Admin\ClientController@destroy')->name('admin.clients.destroy');

    Route::get('users', 'Admin\MobileUserController@index')->name('admin.users.index');
    Route::post('users', 'Admin\MobileUserController@store')->name('admin.users.store');
    Route::get('users/{user}', 'Admin\MobileUserController@show')->name('admin.users.show');
    Route::patch('users/{user}', 'Admin\MobileUserController@update')->name('admin.users.update');
    Route::delete('users/{user}', 'Admin\MobileUserController@destroy')->name('admin.users.destroy');
});
