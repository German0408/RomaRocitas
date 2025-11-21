<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\FamilyController;
use App\Http\Controllers\Admin\OptionController;
use App\Http\Controllers\Admin\ProductsController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\UsersController;
use Illuminate\Support\Facades\Route;

Route::get('/', function(){
    return view('admin.dashboard');
})->name('dashboard');

Route::get('/options',[OptionController::class, 'index'])->name('options.index');

Route::resource('families', FamilyController::class);

Route::resource('categories', CategoryController::class);

Route::resource('subcategories', SubcategoryController::class);

Route::resource('products', ProductsController::class);

Route::resource('users', UsersController::class);

