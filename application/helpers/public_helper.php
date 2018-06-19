<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by Stefan Ho.
 * User: Stefan <xiugang.he@chukou1.com>
 * Date: 2018-06-14 17:15
 */

/**
 * 代码调试
 */
function p() {
    $argc = func_get_args();

    echo '<pre>';
    foreach($argc as $var) {
        print_r($var);
        echo '<br/>';
    }

    echo '</pre>';
    exit;

    return;
}

/**
 * 代码调试
 */
function pr() {
    $argc = func_get_args();
    echo '<pre>';
    foreach($argc as $var) {
        print_r($var);
        echo '<br/>';
    }
    echo '</pre>';
}

/**
 * 返回当前控制器
 * @return CI_Controller|object
 */
function ci() {
    return get_instance();
}

/**
 * 是否在开发环境
 * @return bool
 */
function in_development() {
    return ENVIRONMENT === 'development';
}

/**
 * @param $arr
 * @param $key
 * @param string $default
 * @return mixed|string
 */
function array_value($arr, $key, $default = '') {
    $keys = explode('.', $key);
    $data = $arr;
    foreach($keys as $one_key) {
        if((is_array($data) || $data instanceof ArrayAccess) && isset($data[$one_key])) {
            $data = $data[$one_key];
        } else {
            return $default;
        }
    }

    return $data;
}

/**
 * ajax json输出
 * @param mixed $data 待输出的数据
 * @param int $code 状态或错误码。0为正常或成功，其余有问题
 * @param string $msg 错误等信息
 */
function json_output($data = null, $code = 0, $msg = '') {
    header('Content-Type: text/html;charset=UTF-8');
    echo json_encode(array('code' => $code, 'msg' => $msg, 'data' => $data));
    die;
}

function dhtmlspecialchars($string, $flags = null) {
    if(is_array($string)) {
        foreach($string as $key => $val) {
            $string[$key] = dhtmlspecialchars($val, $flags);
        }
    } else {
        if($flags === null) {
            $string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
            if(strpos($string, '&amp;#') !== false) {
                $string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
            }
        } else {
            if(PHP_VERSION < '5.4.0') {
                $string = htmlspecialchars($string, $flags);
            } else {
                if(strtolower(CHARSET) == 'utf-8') {
                    $charset = 'UTF-8';
                } else {
                    $charset = 'ISO-8859-1';
                }
                $string = htmlspecialchars($string, $flags, $charset);
            }
        }
    }

    return $string;
}
