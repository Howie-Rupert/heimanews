<?php
use think\Route;

Route::get('api/v1/news', 'api/News/index');
Route::get('api/v1/news/:id', 'api/News/detail');
