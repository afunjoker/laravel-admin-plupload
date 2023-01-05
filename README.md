laravel-admin-upload extension
======

## Installation

You can add this library as a local, per-project dependency to your project using [Composer](https://getcomposer.org/):

    composer require afunjoker/laravel-admin-plupload

在项目下的app/Admin/bootstrap.php中注册添加:

    Encore\Admin\Form::extend('laravel_admin_plupload', afunjoker\LaravelAdminPlupload\LaravelAdminPluploadFileField::class);

在表单组件中使用:
app/Admin/SiteController.php中form方法中将
$item = $form->image($attribute->english_name, $attribute->name)->removable()->uniqueName();
替换如下:
````
$form->laravel_admin_plupload($attribute->english_name, $attribute->name);
````