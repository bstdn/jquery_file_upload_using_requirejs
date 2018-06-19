<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by Stefan Ho.
 * User: Stefan <xiugang.he@chukou1.com>
 * Date: 2018-06-18 18:16
 */
class Attachment_model extends MY_Model {

    protected $_table_name  = 'attachment';
    protected $_primary_key = 'aid';

    function __construct() {
        parent::__construct();
    }

    /**
     * @return bool|int|object
     */
    public function get_attach_new_aid() {
        return $this->insert(array('tid' => 0));
    }
}
