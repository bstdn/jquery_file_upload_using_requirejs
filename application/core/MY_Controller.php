<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by Stefan Ho.
 * User: Stefan <xiugang.he@chukou1.com>
 * Date: 2018-06-13 09:51
 */

/**
 * Class MY_Controller
 * @property CI_Router $router
 *
 * 自定义类库:
 * @property MY_Upload $upload
 */
class MY_Controller extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    /**
     * layout，相对于views/layout目录下
     * @var string
     */
    protected $_layout = 'default';

    /**
     * 渲染视图
     * @param array $data
     * @param null $view
     */
    protected function view($data = array(), $view = null) {
        $action_path = $this->action_path();
        if(is_null($view)) {
            $view = implode(DS, $action_path);
        }
        if($this->_layout) {
            $content = $this->load->view($view, $data, true);
            $params = array('content' => $content);
            $this->load->view("layout/{$this->_layout}", $params);
        } else {
            $this->load->view($view, $data);
        }
    }

    /**
     * 返回到当前action的“路径”
     * @param null $sep
     * @return array|string
     */
    public function action_path($sep = null) {
        $path = explode('/', $this->router->directory);
        $path = array_filter($path);
        $path[] = strtolower($this->router->class);
        $path[] = strtolower($this->router->method);

        return is_string($sep) ? implode($sep, $path) : $path;
    }
}
