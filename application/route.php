<?php

use think\Route;

Route::get('api/v1/news','api/news/index');
Route::post('api/v1/login','api/login/index');
Route::post('api/v1/plun','api/news/plun');
Route::post('api/v1/rep','api/news/rep');
Route::get('api/v1/logout','api/login/logout');
Route::get('api/v1/news/:id','api/news/detail');
Route::get('api/v1/reply/:id','api/news/viewreply');
Route::get('api/v1/view/:id','api/news/view');