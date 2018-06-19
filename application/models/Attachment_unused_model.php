<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by Stefan Ho.
 * User: Stefan <xiugang.he@chukou1.com>
 * Date: 2018-06-18 18:16
 */
class Attachment_unused_model extends MY_Model {

    protected $_table_name  = 'attachment_unused';
    protected $_primary_key = 'aid';

    function __construct() {
        parent::__construct();
    }
}
