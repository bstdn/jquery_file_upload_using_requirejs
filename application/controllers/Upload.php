<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by Stefan Ho.
 * User: Stefan <xiugang.he@chukou1.com>
 * Date: 2018-06-13 09:50
 */
class Upload extends MY_Controller {

    private $_thread_model_name = 'Thread_model';
    /** @var Thread_model $_thread_model */
    private $_thread_model = '';

    function __construct() {
        parent::__construct();

        $this->load->model($this->_thread_model_name);
        $model_name = $this->_thread_model_name;
        $this->_thread_model = $this->$model_name;

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
                        'name'       => $upload_data['client_name'],
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
                        'name'         => $upload_data['client_name'],
                        'size'         => $upload_data['file_size'],
                        'type'         => $upload_data['file_type'],
                        'url'          => $this->upload->attach_dir . $upload_data['attach_path'],
                        'thumbnailUrl' => $this->upload->attach_dir . $upload_data['thumbnail_path'],
                        'deleteUrl'    => '',
                        'deleteType'   => 'DELETE',
                    ),
                ),
            );
        }

        echo json_encode($result);
    }

    public function file_upload() {
        if($this->input->is_post()) {
            $this->_file_upload_post();
        } else {
            $this->view();
        }
    }

    protected function _file_upload_post() {
        $input = $this->input->post();
        $subject = dhtmlspecialchars(trim(array_value($input, 'subject')));
        $subject = !empty($subject) ? str_replace("\t", ' ', $subject) : $subject;
        if(empty($subject)) {
            show_error('subject_error');
        }

        $params = array(
            'subject' => $subject,
        );
        $tid = $this->_thread_model->new_thread($params);

        if(!$tid) {
            show_error('new_thread_error');
        }

        if($aids = array_value($input, 'aids')) {
            $this->_thread_model->new_thread_update($tid, $aids);
        }

        echo 'Success!';
    }

    public function ajax_upload() {
        $result = $this->_thread_model->do_upload();

        echo json_encode($result);
    }
}
