<?php

use App\Http\Controllers\FormsDemoController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::controller(FormsDemoController::class)
        ->prefix('forms-demo')
        ->name('forms-demo.')
        ->where([
            'demo' => implode('|', array_keys(FormsDemoController::DEMOS)),
            'entry' => '[0-9]+',
        ])
        ->group(function () {
            Route::get('{demo?}', 'index')->name('index');
            Route::get('{demo}/create', 'create')->name('create');
            Route::post('{demo}', 'store')->name('store');
            Route::get('{demo}/{entry}', 'show')->name('show');
            Route::get('{demo}/{entry}/edit', 'edit')->name('edit');
            Route::put('{demo}/{entry}', 'update')->name('update');
            Route::delete('{demo}/{entry}', 'destroy')->name('destroy');
            Route::get('{demo}/{entry}/file', 'file')->name('file');
        });
});

require __DIR__.'/settings.php';
