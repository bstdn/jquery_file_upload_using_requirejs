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
