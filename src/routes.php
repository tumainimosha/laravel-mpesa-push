<?php

use \Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Tumainimosha\MpesaPush\Http\Controllers'], function () {

    /**
     * Process Callback Route.
     */
    $callback_path = config('mpesa-push.callback_path');
    $callback_middlewares = config('mpesa-push.callback_middleware');

    Route::post($callback_path, 'CallbackController')
        ->middleware($callback_middlewares);
});
