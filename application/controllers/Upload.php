<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by Stefan Ho.
 * User: Stefan <xiugang.he@chukou1.com>
 * Date: 2018-06-13 09:50
 */
class Upload extends MY_Controller {

    function __construct() {
        parent::__construct();

        $this->_layout = null;
    }

    public function index() {
        $this->view();
    }

    public function index_upload() {
        $this->view();
    }

    public function img_upload() {
        $this->load->config('upload');
        $_upload_config = config_item('upload');
        $this->load->library('upload', $_upload_config);
        $this->upload->get_target_dir($this->upload->folder_type);
        $res = $this->upload->upload();
        $upload_data = $this->upload->get_data();
        if(!$res) {
            $result = array(
                'files' => array(
                    array(
                        'name'       => $upload_data['file_name'],
                        'size'       => $upload_data['file_size'],
                        'type'       => $upload_data['file_type'],
                        'error'      => $this->upload->display_errors('', ''),
                        'deleteUrl'  => '',
                        'deleteType' => 'DELETE',
                    ),
                ),
            );
        } else {
            $result = array(
                'files' => array(
                    array(
                        'name'         => $upload_data['file_name'],
                        'size'         => $upload_data['file_size'],
                        'type'         => $upload_data['file_type'],
                        'url'          => $upload_data['attach_path'],
                        'thumbnailUrl' => $upload_data['thumbnail_path'],
                        'deleteUrl'    => '',
                        'deleteType'   => 'DELETE',
                    ),
                ),
            );
        }

        echo json_encode($result);
    }
}
