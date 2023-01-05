<style>
    ul {
        list-style: none;
    }

    #file-list-{{$id}}  {
        overflow: hidden;
        padding-left: initial;
    }

    #file-list-{{$id}} li {
        width: 160px;
        float: left;
        position: relative;
        height: inherit;
        margin-bottom: inherit;
    }

    #file-list-{{$id}} li a {
        width: 150px;
        height: 150px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        border: 1px solid #ccc;
        padding: 5px;
    }

    .close {
        background-image: url("{{asset('/vendor/afunjoker/laravel-admin-plupload/jquery.plupload.queue/img/delete.gif')}}");
        width: 30px;
        height: 30px;
        background-size: contain;
        position: absolute;
        right: 2%;
        top: 0;
    }

    #file-list-{{$id}} li a img {
        max-width: 100%;
        max-height: 100%;
    }
</style>
<div class="form-group">
    <label for="{{$id}}" class="col-sm-2 control-label">{{$label}}</label>
    <div class="col-sm-8">
        <input type="hidden" id="{{$id}}" name="{{$id}}" value="{{$value}}"/>
        <div id="container">
            <div id="console-{{$id}}"></div>
            <button id="pickfiles-{{$id}}">选择文件</button>
        </div>
        <ul id="file-list-{{$id}}"></ul>
    </div>
</div>
<!-- production -->
<script type="text/javascript" src="{{asset('plupload.full.min.js')}}"></script>

<!-- debug-->
{{--<script type="text/javascript" src="/vendor/afunjoker/laravel-admin-plupload/moxie.js"></script>--}}
{{--<script type="text/javascript" src="/vendor/afunjoker/laravel-admin-plupload/plupload.dev.js"></script>--}}
<script>
    var uploader = new plupload.Uploader({
        runtimes: 'html5,flash,silverlight,html4',//上传方式顺序优先级
        browse_button: 'pickfiles-{{$id}}', // 选择图片按钮id
        container: document.getElementById('container'), // 容器
        url: window.prefix + 'laravel-admin-plupload/upload',//接口
        flash_swf_url: {{asset('Moxie.swf')}},
        silverlight_xap_url: {{asset('Moxie.xap')}},
        chunk_size: window.chunk_size,

        filters: {
            max_file_size: window.max_file_size,//限制文件上传大小
            mime_types: window.file_type,//限制文件上传格式
        },
        multipart_params: {
            '_token': "{{csrf_token()}}"
        },
        init: {
            PostInit: function () {
                if ($("#{{$id}}").val()){
                    $('#pickfiles-{{$id}}').css('display','none')
                    $('#file-list-{{$id}}').append('<li id="pre-file-{{$id}}"><span class="close"></span></li>')
                    $('#pre-file-{{$id}}').append('<a><img src="' + window.file_url + '{{$value}}" alt=""></a>');
                }
            },
            FilesAdded: function (up, files) {//文件选择之后的触发的方法
                document.getElementById('console-{{$id}}').textContent = '';
                let len = files.length;
                for (let i = 0; i < len; i++) {
                    let file_type = files[i].type
                    file_type = file_type.split('/')[0]
                    if(file_type === 'image' || file_type === 'video' || file_type=== 'audio'){
                        file_type += 's'
                    }else {
                        file_type = 'files'
                    }
                    uploader.setOption("multipart_params",{file_type});
                    //构造html来更新UI
                    const html = '<li id="file-' + files[i].id + '"><span class="close"></span></li>';
                    $(html).appendTo('#file-list-{{$id}}');
                    !function (i) {
                        previewImage(files[i], function (imgsrc) {
                            $('#file-' + files[i].id).append('<a><img src="' + imgsrc + '"  alt=""/></a>');
                            $('#file-' + files[i].id).append('<b id="file-progress-' + files[i].id+'"'+'>...</b>');
                        })
                    }(i);
                }
                $('#pickfiles-{{$id}}').css('display', 'none')
                uploader.start();
            },
            //上传过程中
            UploadProgress: function (up, file) {
                $('#file-progress-'+file.id).html(`<span>文件上传进度: ${file.percent}%</span>`);
            },
            //上传队列中所有文件都上传完成后
            FileUploaded:function (uploader,file,responseObj) {
                let res = JSON.parse(responseObj.response)
                $("#{{$id}}").val(res.path)
            },

            Error: function (up, err) {
                document.getElementById('console-{{$id}}').appendChild(document.createTextNode("\nError #" + err.code + ": " + err.message));
            }
        }
    });
    //plupload中为我们提供了mOxie对象
    //有关mOxie的介绍和说明请看：https://github.com/moxiecode/moxie/wiki/API
    //file为plupload事件监听函数参数中的file对象,callback为预览图片准备完成的回调函数
    function previewImage(file, callback) {
        if (!file || !/image\//.test(file.type)) return; //确保文件是图片
        if (file.type === 'image/gif') { //gif使用FileReader进行预览,因为mOxie.Image只支持jpg和png
            let gif = new moxie.file.FileReader();
            gif.onload = function () {
                callback(gif.result);
                gif.destroy();
                gif = null;
            };
            gif.readAsDataURL(file.getSource());
        } else {
            let image = new moxie.image.Image();
            image.onload = function () {
                image.downsize(150, 150);//先压缩一下要预览的图片,宽300，高300
                const imgSrc = image.type === 'image/jpeg' ? image.getAsDataURL('image/jpeg', 80) : image.getAsDataURL(); //得到图片src,实质为一个base64编码的数据
                callback && callback(imgSrc); //callback传入的参数为预览图片的url
                image.destroy();
                image = null;
            };
            image.load(file.getSource());
        }
    }

    //移除图片
    $("#file-list-{{$id}}").on('click', ".close", function () {
        $(this).parent().remove();
        $("#{{$id}}").val('')
        $('#pickfiles-{{$id}}').css('display', 'inline-block')
    });
    uploader.init();

</script>