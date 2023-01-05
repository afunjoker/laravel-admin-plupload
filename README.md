laravel-admin-upload extension
======

## Installation

通过 [Composer](https://getcomposer.org/)安装扩展:

    composer require afunjoker/laravel-admin-plupload

在config/app.php中的providers下添加如下:

    afunjoker\LaravelAdminPlupload\LaravelAdminPluploadServiceProvider::class,

发布项目配置文件:

    php artisan vendor:publish --provider=afunjoker\LaravelAdminPlupload\LaravelAdminPluploadServiceProvider

在项目下的app/Admin/bootstrap.php中注册添加:

    Encore\Admin\Form::extend('laravel_admin_plupload', afunjoker\LaravelAdminPlupload\LaravelAdminPluploadFileField::class);

在表单组件中使用:
app/Admin/SiteController.php中form方法中将
$item = $form->image($attribute->english_name, $attribute->name)->removable()->uniqueName();
替换如下:
````
$form->laravel_admin_plupload($attribute->english_name, $attribute->name);
````