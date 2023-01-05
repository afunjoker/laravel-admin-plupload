<?php

return [
    'chunkSize' => '2mb',//上传文件切片大小,单位m
    'maxFileSize' => '200mb',//上传文件总大小限制
    'fileType' => [//限制文件上传格式
        ['title' => "Image files", 'extensions' => "jpg,gif,png,jpeg"],
        ['title' => "Zip files", 'extensions' => "zip"],
        ['title' => "Video files", 'extensions' => "mp4"],
        ['title' => "Audio files", 'extensions' => "mp3"],
    ]
];
