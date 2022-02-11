<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::get('/subscribe', function () {
    return view('subscribe', [
         'intent' => auth()->user()->createSetupIntent()
    ]);
})->middleware(['auth'])->name('subscribe');

Route::post('/subscribe', function (Request $request) {
    //dd($request->all());
    //internal_name_product of product should be stored in database

    $request->user()->newSubscription(
        'internal_name_product ', $request->plan
    )->create($request->paymentMethod);

    return redirect()->route('subscribe')->with(['success' => 'Payment made']);
})->middleware(['auth'])->name('subscribe.store');


require __DIR__.'/auth.php';
