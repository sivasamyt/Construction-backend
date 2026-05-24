<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/{any}', function () {
    $spaPath = public_path('spa/index.html');

    if (file_exists($spaPath)) {
        return file_get_contents($spaPath);
    }

    return view('welcome');
})->where('any', '^(?!api|up).*$');
