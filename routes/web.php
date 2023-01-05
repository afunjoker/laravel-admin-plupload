<?php

use afunjoker\LaravelAdminPlupload\Http\Controllers\LaravelAdminPluploadController;

Route::post('{customer}/{project}/{model}/laravel-admin-plupload/upload', LaravelAdminPluploadController::class.'@upload');