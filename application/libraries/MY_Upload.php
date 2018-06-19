<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by Stefan Ho.
 * User: Stefan <xiugang.he@chukou1.com>
 * Date: 2018-06-15 09:08
 */

/**
 * MY_Upload Class
 *
 * @package     CodeIgniter
 * @subpackage  Libraries
 * @category    Uploads
 * @author      bstdn
 */
class MY_Upload extends CI_Upload {

    /**
     * Folder type
     *
     * @var string
     */
    public $folder_type = '';

    /**
     * Attach dir
     *
     * @var string
     */
    public $attach_dir = '';

    /**
     * Old upload path
     *
     * @var string
     */
    protected $_old_upload_path = '';

    /**
     * Thumbnail width
     *
     * @var string
     */
    public $thumbnail_width = '';

    /**
     * Thumbnail height
     *
     * @var string
     */
    public $thumbnail_height = '';

    /**
     * Thumbnail name
     *
     * @var string
     */
    public $thumbnail_name = '';

    /**
     * MY_Upload constructor.
     * @param array $config
     * @return void
     */
    public function __construct($config = array()) {
        parent::__construct($config);

        empty($config) OR $this->initialize($config, false);
        $this->_mimes =& get_mimes();
        $this->_CI =& get_instance();
        log_message('debug', 'MY_Upload Class Initialized');
    }

    /**
     * Perform the file upload
     *
     * @param string $field
     * @return bool
     */
    public function upload($field = 'files') {
        // Is $_FILES[$field] set? If not, no reason to continue.
        if(isset($_FILES[$field])) {
            $_file = $_FILES[$field];
        } else {
            $this->set_error('upload_no_file_selected', 'debug');

            return false;
        }

        // Is the upload path valid?
        if(!$this->validate_upload_path()) {
            // errors will already be set by validate_upload_path() so just return FALSE
            return false;
        }

        // One by one upload
        if(is_array($_file['tmp_name'])) {
            $_file = array(
                'name'     => $_file['name'][0],
                'type'     => $_file['type'][0],
                'tmp_name' => $_file['tmp_name'][0],
                'error'    => $_file['error'][0],
                'size'     => $_file['size'][0],
            );
        }

        // Was the file able to be uploaded? If not, determine the reason why.
        if(!is_uploaded_file($_file['tmp_name'])) {
            $error = isset($_file['error']) ? $_file['error'] : 4;

            switch($error) {
                case UPLOAD_ERR_INI_SIZE:
                    $this->set_error('upload_file_exceeds_limit', 'info');
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $this->set_error('upload_file_exceeds_form_limit', 'info');
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $this->set_error('upload_file_partial', 'debug');
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $this->set_error('upload_no_file_selected', 'debug');
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $this->set_error('upload_no_temp_directory', 'error');
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $this->set_error('upload_unable_to_write_file', 'error');
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $this->set_error('upload_stopped_by_extension', 'debug');
                    break;
                default:
                    $this->set_error('upload_no_file_selected', 'debug');
                    break;
            }

            return false;
        }

        // Set the uploaded data as class variables
        $this->file_temp = $_file['tmp_name'];
        $this->file_size = $_file['size'];

        // Skip MIME type detection?
        if($this->detect_mime !== false) {
            $this->_file_mime_type($_file);
        }

        $this->file_type = preg_replace('/^(.+?);.*$/', '\\1', $this->file_type);
        $this->file_type = strtolower(trim(stripslashes($this->file_type), '"'));
        $this->file_name = $this->_prep_filename($_file['name']);
        $this->file_ext = $this->get_extension($this->file_name);
        $this->client_name = $this->file_name;

        // Is the file type allowed to be uploaded?
        if(!$this->is_allowed_filetype()) {
            $this->set_error('upload_invalid_filetype', 'debug');

            return false;
        }

        // if we're overriding, let's now make sure the new name and type is allowed
        if($this->_file_name_override !== '') {
            $this->file_name = $this->_prep_filename($this->_file_name_override);

            // If no extension was provided in the file_name config item, use the uploaded one
            if(strpos($this->_file_name_override, '.') === false) {
                $this->file_name .= $this->file_ext;
            } else {
                // An extension was provided, let's have it!
                $this->file_ext = $this->get_extension($this->_file_name_override);
            }

            if(!$this->is_allowed_filetype(true)) {
                $this->set_error('upload_invalid_filetype', 'debug');

                return false;
            }
        }

        // Convert the file size to kilobytes
        if($this->file_size > 0) {
            $this->file_size = round($this->file_size / 1024, 2);
        }

        // Is the file size within the allowed maximum?
        if(!$this->is_allowed_filesize()) {
            $this->set_error('upload_invalid_filesize', 'info');

            return false;
        }

        // Are the image dimensions within the allowed size?
        // Note: This can fail if the server has an open_basedir restriction.
        if(!$this->is_allowed_dimensions()) {
            $this->set_error('upload_invalid_dimensions', 'info');

            return false;
        }

        // Sanitize the file name for security
        $this->file_name = $this->_CI->security->sanitize_filename($this->file_name);

        // Truncate the file name if it's too long
        if($this->max_filename > 0) {
            $this->file_name = $this->limit_filename_length($this->file_name, $this->max_filename);
        }

        // Remove white spaces in the name
        if($this->remove_spaces === true) {
            $this->file_name = preg_replace('/\s+/', '_', $this->file_name);
        }

        if($this->file_ext_tolower && ($ext_length = strlen($this->file_ext))) {
            // file_ext was previously lower-cased by a get_extension() call
            $this->file_name = substr($this->file_name, 0, -$ext_length) . $this->file_ext;
        }

        /*
         * Validate the file name
         * This function appends an number onto the end of
         * the file if one with the same name already exists.
         * If it returns false there was a problem.
         */
        $this->orig_name = $this->file_name;
        if(false === ($this->file_name = $this->set_filename($this->upload_path, $this->file_name))) {
            return false;
        }

        /*
         * Run the file through the XSS hacking filter
         * This helps prevent malicious code from being
         * embedded within a file. Scripts can easily
         * be disguised as images or other file types.
         */
        if($this->xss_clean && $this->do_xss_clean() === false) {
            $this->set_error('upload_unable_to_write_file', 'error');

            return false;
        }

        /*
         * Move the file to the final destination
         * To deal with different server configurations
         * we'll attempt to use copy() first. If that fails
         * we'll use move_uploaded_file(). One of the two should
         * reliably work in most environments
         */
        if(!@copy($this->file_temp, $this->upload_path . $this->file_name)) {
            if(!@move_uploaded_file($this->file_temp, $this->upload_path . $this->file_name)) {
                $this->set_error('upload_destination_error', 'error');

                return false;
            }
        }

        /*
         * Set the finalized image dimensions
         * This sets the image width/height (assuming the
         * file was an image). We use this information
         * in the "data" function.
         */
        $this->set_image_properties($this->upload_path . $this->file_name);

        /**
         * thumbnail
         */
        if($this->thumbnail_width > 0 && $this->thumbnail_height > 0) {
            $this->cut_out_img($this->thumbnail_width, $this->thumbnail_height);
        }

        return true;
    }

    /**
     * Finalized Data Array
     *
     * Returns an associative array containing all of the information
     * related to the upload, allowing the developer easy access in one array.
     *
     * @param   string $index
     * @return  mixed
     */
    public function get_data($index = null) {
        $data = array(
            'file_name'      => $this->file_name,
            'file_type'      => $this->file_type,
            'file_path'      => $this->upload_path,
            'full_path'      => $this->upload_path . $this->file_name,
            'attach_dir'     => $this->get_target_dir($this->folder_type),
            'attach_path'    => $this->get_target_dir($this->folder_type) . $this->file_name,
            'thumbnail_name' => $this->thumbnail_name,
            'thumbnail_path' => $this->thumbnail_name ? $this->get_target_dir($this->folder_type) . $this->thumbnail_name : '',
            'raw_name'       => substr($this->file_name, 0, -strlen($this->file_ext)),
            'orig_name'      => $this->orig_name,
            'client_name'    => $this->client_name,
            'file_ext'       => $this->file_ext,
            'file_size'      => $this->file_size,
            'is_image'       => $this->is_image(),
            'image_width'    => $this->image_width,
            'image_height'   => $this->image_height,
            'image_type'     => $this->image_type,
            'image_size_str' => $this->image_size_str,
        );

        if(!empty($index)) {
            return isset($data[$index]) ? $data[$index] : null;
        }

        return $data;
    }

    /**
     * Get Target Dir
     *
     * @param string $type
     * @param bool $check_exists
     * @return string
     */
    public function get_target_dir($type = 'common', $check_exists = true) {
        $sub_dir1 = date('Y-m-d');
        $sub_dir = $type . DS . $sub_dir1 . DS;
        empty($this->_old_upload_path) && $this->_old_upload_path = $this->upload_path;
        $upload_path = $this->_old_upload_path . $sub_dir;
        $this->set_upload_path($upload_path);

        $check_exists && $this->make_dir($upload_path);

        return $sub_dir;
    }

    /**
     * Set Folder type
     *
     * @param $type
     * @return $this
     */
    public function set_folder_type($type) {
        $this->folder_type = $type;

        return $this;
    }

    /**
     * Make dir
     *
     * @param $dir
     * @param bool $index
     * @return bool
     */
    public function make_dir($dir, $index = true) {
        $res = true;
        if(file_exists($dir)) {
            return $res;
        }
        if(!$this->make_dir(dirname($dir))) {
            return false;
        }

        $res = @mkdir($dir, 0777);
        $index && @touch($dir . '/index.html');

        return $res;
    }

    /**
     * Cut image
     *
     * @param $thumbnail_width
     * @param $thumbnail_height
     * @return $this
     */
    public function cut_out_img($thumbnail_width, $thumbnail_height) {
        if($this->is_image() && function_exists('getimagesize')) {
            $name_ext = explode('.', $this->file_name);
            $ext = array_pop($name_ext);
            $name = implode('.', $name_ext);
            $this->thumbnail_name = $name . "_{$thumbnail_width}*{$thumbnail_height}.{$ext}";
            $thumbnail_path = $this->upload_path . $this->thumbnail_name;
            $this->copy_img($this->upload_path . $this->file_name, $thumbnail_width, $thumbnail_height, $thumbnail_path);
        }

        return $this;
    }

    /**
     * Copy image
     *
     * @param $source_file
     * @param $new_width
     * @param $new_height
     * @param null $new_file_name
     * @param int $quality
     * @param int $keep_ratio
     * @return bool
     */
    public function copy_img($source_file, $new_width, $new_height, $new_file_name = null, $quality = 90, $keep_ratio = 0) {
        if(!function_exists('getimagesize')) {
            return false;
        }

        list($width, $height, $type) = getimagesize($source_file);

        $_new_width = $new_width;
        $_new_height = $new_height;
        $skewing_x = $skewing_y = 0;

        if($keep_ratio != 0) {
            $ratio = round(($height / $width) * 10);
            if($ratio < 12) {
                if($new_width > 0) {
                    $_new_width = ceil($new_height / ($height / $width));
                } else {
                    $new_width = ceil($height / $keep_ratio);
                    $_new_width = $width;
                    $new_height = $_new_height = $height;
                }

                $skewing_x = (int)(($_new_width - $new_width) / 2);
            } elseif($ratio > 12) {
                if($new_height > 0) {
                    $_new_height = ceil($new_width * ($height / $width));
                } else {
                    $new_height = ceil($width * $keep_ratio);
                    $_new_height = $height;
                    $new_width = $_new_width = $width;
                }

                $skewing_y = (int)(($_new_height - $new_height) / 2);
            }
        }

        switch(image_type_to_extension($type)) {
            case '.bmp':
                $source = imagecreatefromwbmp($source_file);
                break;
            case '.gif':
                $source = imagecreatefromgif($source_file);
                break;
            case '.png':
                $source = imagecreatefrompng($source_file);
                break;
            case '.jpeg':
            case '.jpg':
                $source = imagecreatefromjpeg($source_file);
                break;
        }

        if($new_width && $new_height) {
            $thumb = imagecreatetruecolor($new_width, $new_height);
            imagecopyresampled($thumb, $source, -($skewing_x), -($skewing_y), 0, 0, $_new_width, $_new_height, $width, $height);
        } else {
            $thumb = $source;
        }

        imagejpeg($thumb, $new_file_name, $quality);

        return true;
    }
}
