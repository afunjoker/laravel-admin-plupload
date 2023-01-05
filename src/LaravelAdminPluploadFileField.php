<?php

namespace afunjoker\LaravelAdminPlupload;

use App\Helper;
use Encore\Admin\Form\Field;
use Encore\Admin\Form\Field\UploadField;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Client\HttpClientException;

class LaravelAdminPluploadFileField extends Field
{
    public $view = 'laravel-admin-plupload::laravel-admin-plupload';

    protected $fileType;//文件类型
    protected $chunkSize;//文件切片大小
    protected $maxFileSize;//文件最大限制

    use UploadField;

    /**
     * Storage instance.
     *
     * @var Filesystem
     */
    protected $storage = '';


    public function __construct($column, array $arguments = [])
    {
        $this->chunkSize = config('laravel_admin_plupload.chunkSize');
        $this->maxFileSize = config('laravel_admin_plupload.maxFileSize');
        $this->fileType = json_encode(config('laravel_admin_plupload.fileType'));
        parent::__construct($column, $arguments);
        $this->initStorage();
    }

    /**
     * @throws HttpClientException
     */
    public function render()
    {
        $prefix = trim(config('admin.route.prefix'), '/');
        $prefix = $prefix ? '/' . $prefix : '';
        $prefix.= '/'.Helper::customer()->english_name . '/' . Helper::project()->english_name .'/'.Helper::model()->english_name.'/';
        $this->script = <<<SRC
            (function () {
                window.chunk_size = '{$this->chunkSize}';//文件切片大小
                window.prefix = '{$prefix}';//后台前缀
                window.file_url = '{$this->storage->url('')}';//原文件地址
                window.max_file_size = '{$this->maxFileSize}';//最大文件大小限制
                window.file_type = '{$this->fileType}';//上传文件类型限制
            })();
SRC;

        return parent::render();
    }
}

