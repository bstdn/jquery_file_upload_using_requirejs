<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by Stefan Ho.
 * User: Stefan <xiugang.he@chukou1.com>
 * Date: 2018-06-18 17:40
 */
class Thread_model extends MY_Model {

    protected $_table_name  = 'thread';
    protected $_primary_key = 'tid';

    private $_attachment_model_name = 'Attachment_model';
    /** @var Attachment_model $_attachment_model */
    private $_attachment_model = '';

    private $_attachment_n_model_name = 'Attachment_n_model';
    /** @var Attachment_n_model $_attachment_n_model */
    private $_attachment_n_model = '';

    private $_attachment_unused_model_name = 'Attachment_unused_model';
    /** @var Attachment_unused_model $_attachment_unused_model */
    private $_attachment_unused_model = '';

    function __construct() {
        parent::__construct();

        $this->load->model($this->_attachment_model_name);
        $model_name = $this->_attachment_model_name;
        $this->_attachment_model = ci()->$model_name;

        $this->load->model($this->_attachment_n_model_name);
        $model_name = $this->_attachment_n_model_name;
        $this->_attachment_n_model = ci()->$model_name;

        $this->load->model($this->_attachment_unused_model_name);
        $model_name = $this->_attachment_unused_model_name;
        $this->_attachment_unused_model = ci()->$model_name;
    }

    /**
     * @param string $field
     * @return array
     */
    public function do_upload($field = 'files') {
        $this->load->config('upload');
        $_upload_config = config_item('upload');
        $this->load->library('upload', $_upload_config);
        $this->upload->get_target_dir($this->upload->folder_type);
        $res = $this->upload->upload($field);
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
            $aid = $this->_attachment_model->get_attach_new_aid();
            $insert = array(
                'aid'        => $aid,
                'dateline'   => TIMESTAMP,
                'filename'   => $upload_data['client_name'],
                'filesize'   => $upload_data['file_size'],
                'attachment' => $upload_data['attach_path'],
                'isimage'    => $upload_data['is_image'],
                'width'      => $upload_data['image_width'],
            );
            $this->_attachment_unused_model->insert($insert);
            $result = array(
                'files' => array(
                    array(
                        'aid'          => $aid,
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

        return $result;
    }

    public function new_thread($params) {
        if(!isset($params['dateline'])) {
            $params['dateline'] = TIMESTAMP;
        }
        return $this->insert($params);
    }

    public function new_thread_update($tid, $aids) {
        if(!$tid OR empty($aids)) {
            return false;
        }
        $this->_attachment_model->where_in('aid', $aids)->set('tid', $tid)->update();
        $attach_list = $this->_attachment_unused_model->where_in('aid', $aids)->query();
        foreach($attach_list as &$attach) {
            $attach['tid'] = $tid;
        }
        unset($attach);
        $this->_attachment_n_model->insert_batch($attach_list);
        $this->_attachment_unused_model->where_in('aid', $aids)->delete();

        return true;
    }
}
