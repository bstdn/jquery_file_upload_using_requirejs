<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by Stefan Ho.
 * User: Stefan <xiugang.he@chukou1.com>
 * Date: 2018-06-19 11:49
 */
class Attachment_n_model extends MY_Model {

    protected $_table_name  = 'attachment_n';
    protected $_primary_key = 'aid';

    function __construct() {
        parent::__construct();
    }
}
