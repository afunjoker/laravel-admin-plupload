<?php

namespace afunjoker\LaravelAdminPlupload\Http\Controllers;

use App\Helper;
use Encore\Admin\Form\Field\UploadField;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class LaravelAdminPluploadController extends Controller
{
    use UploadField;
    private $config;

    public function __construct()
    {
        $this->config = config('laravel_admin_plupload');
        $this->initStorage();
    }
    /**
     * Default directory for file to upload.
     *
     * @return mixed
     */
    public function defaultDirectory()
    {
        return config('admin.upload.directory.file');
    }

    /**
     * 上传文件接口
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function upload(Request $request): JsonResponse
    {
        if ($request->input('chunks') == 1) {
            $path = $this->saveSingleFile($request);
        }else {
            $path = $this->uploadChunk($request);
        }
        return $this->returnResult($path);
    }

    private function saveSingleFile(Request $request): string
    {
        $name = date('YmdHis').'_'.$request->input('name');
        $path = $request->input('file_type').'/'.$name;
        $this->storage->putFileAs(
            Helper::customer()->english_name . '/' . Helper::project()->english_name . '/' . $request->input('file_type'),
            $request->file('file'),
            $name
        );
        return $path;
    }

    /**
     * 上传块
     * @param Request $request
     * @return string
     * @throws Exception
     */
    private function uploadChunk(Request $request): string
    {
        try {
            // 接收相关数据
            $file = $request->file('file');
            $name = '';
            Storage::disk(config('admin.upload.disk'))->putFileAs(Helper::customer()->english_name . '/' . Helper::project()->english_name . '/chunk_file/'. md5($request->input('name')) , $file,$request->chunk.'.part');
            if ($request->input('chunk') + 1 == $request->input('chunks')){
                $name = $this->mergeChunk($request);
            }
            return $name;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    private function returnResult($path): JsonResponse
    {
        return response()->json([
            'hash' => md5($path),
            'path' => $path
        ]);
    }

    /**
     * 合并块成一个文件
     */
    private function mergeChunk(Request $request): string
    {
        $disk = config('admin.upload.disk');
        $file_name = $request->input('name');
        // 找出分片文件
        $dir = Storage::disk($disk)->path(Helper::customer()->english_name . '/' . Helper::project()->english_name . '/chunk_file/' . md5($file_name));
        // 获取分片文件内容
        $block_info = scandir($dir);
        // 除去无用文件
        foreach ($block_info as $key => $block) {
            if ($block == '.' || $block == '..')
            unset($block_info[$key]);
        }
        // 数组按照正常规则排序
        natsort($block_info);

        $save_dir = Storage::disk($disk)->path(Helper::customer()->english_name . '/' . Helper::project()->english_name. '/' . $request->input('file_type'));
        $name = date('YmdHis').'_'.$file_name;
        //先创建一个空文件
        Storage::disk($disk)->put(Helper::customer()->english_name . '/' . Helper::project()->english_name . '/' . $request->input('file_type') . '/' . $name, '');

        $save_file = $save_dir . '/' . $name;
        // 开始写入
        $out = @fopen($save_file, "wb");
        // 增加文件锁
        if (flock($out, LOCK_EX)) {
            foreach ($block_info as $b) {
                // 读取文件
                if (!$in = @fopen($dir . '/' . $b, "rb")) {
                    break;
                }
                // 写入文件
                while ($buff = fread($in, 4096)) {
                    fwrite($out, $buff);
                }
                @fclose($in);
            }
            flock($out, LOCK_UN);
        }
        @fclose($out);
        //然后删除那个文件夹
        $this->removeDir($dir);
        return $request->input('file_type') . '/' . $name;
    }

    private function removeDir($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        //先删除文件
        $block_info = scandir($dir);
        // 除去无用文件
        foreach ($block_info as $block) {
            if ($block == '.' || $block == '..') {
                continue;
            }
            unlink($dir . '/' . $block);
        }
        rmdir($dir);
    }
}