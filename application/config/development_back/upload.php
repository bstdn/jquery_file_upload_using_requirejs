<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by Stefan Ho.
 * User: Stefan <xiugang.he@chukou1.com>
 * Date: 2018-06-14 18:55
 */
$config['upload'] = array(
    'folder_type'      => 'common', //图片分类目录名
    'attach_dir'       => base_url() . 'build/upload/', //本地附件 URL 地址，可为当前 URL 下的相对地址或 http:// 开头的绝对地址
    'upload_path'      => FCPATH . 'build/upload/', //文件上传的位置，必须是可写的，可以是相对路径或绝对路径
    'allowed_types'    => 'bmp|gif|png|jpeg|jpg', //允许上的文件 MIME 类型，通常文件的后缀名可作为 MIME 类型 可以是数组，也可以是以管道符（|）分割的字符串
    'max_size'         => 2048, //允许上传文件大小的最大值（单位 KB），设置为 0 表示无限制 注意：大多数 PHP 会有它们自己的限制值，定义在 php.ini 文件中 通常是默认的 2 MB （2048 KB）。
    'overwrite'        => true, //如果设置为 TRUE ，上传的文件如果和已有的文件同名，将会覆盖已存在文件 如果设置为 FALSE ，将会在文件名后加上一个数字
    'encrypt_name'     => true, //如果设置为 TRUE ，文件名将会转换为一个随机的字符串 如果你不希望上传文件的人知道保存后的文件名，这个参数会很有用
    'thumbnail_width'  => 64, //缩略图宽度，设置为 0 表示不生成缩略图
    'thumbnail_height' => 64, //缩略图调试，设置为 0 表示不生成缩略图
);
