<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by Stefan Ho.
 * User: Stefan <xiugang.he@chukou1.com>
 * Date: 2018-06-18 17:25
 */

/**
 * MY_Model Class
 *
 * @package Transformers
 * @subpackage Libraries
 * @category Libraries
 * @author bstdn
 *
 * @method MY_Model having($key, $value = null, $escape = null)
 * @method MY_Model limit($value, $offset = false)
 * @method MY_Model where($key, $value = null, $escape = null)
 * @method MY_Model or_group_start()
 * @method MY_Model offset($offset)
 * @method MY_Model or_where_not_in($key = null, $values = null, $escape = null)
 * @method MY_Model distinct($val = true)
 * @method MY_Model select_sum($select = '', $alias = '')
 * @method MY_Model where_in($key = null, $values = null, $escape = null)
 * @method MY_Model select_avg($select = '', $alias = '')
 * @method MY_Model like($field, $match = '', $side = 'both', $escape = null)
 * @method MY_Model select_min($select = '', $alias = '')
 * @method MY_Model group_by($by, $escape = null)
 * @method MY_Model not_like($field, $match = '', $side = 'both', $escape = null)
 * @method MY_Model or_not_group_start()
 * @method MY_Model or_like($field, $match = '', $side = 'both', $escape = null)
 * @method MY_Model select_max($select = '', $alias = '')
 * @method MY_Model or_where($key, $value = null, $escape = null)
 * @method MY_Model or_having($key, $value = null, $escape = null)
 * @method MY_Model group_end()
 * @method MY_Model order_by($orderby, $direction = '', $escape = null)
 * @method MY_Model where_not_in($key = null, $values = null, $escape = null)
 * @method MY_Model or_where_in($key = null, $values = null, $escape = null)
 * @method MY_Model or_not_like($field, $match = '', $side = 'both', $escape = null)
 * @method MY_Model set($key, $value = '', $escape = null)
 * @method MY_Model group_start($not = '', $type = 'AND ')
 * @method MY_Model not_group_start()
 */
class MY_Model extends CI_Model {

    protected $_db;
    protected $_table_name;
    protected $_table_pre;
    protected $_table_alias;
    protected $_error;

    /**
     * Primary key
     *
     * @var string
     */
    protected $_primary_key = '';

    /**
     * create time field
     *
     * @var string
     */
    protected $_created = '';

    /**
     * update time field
     *
     * @var string
     */
    protected $_updated = '';

    /**
     * reset query
     *
     * @var bool
     */
    protected $_re_query = false;

    public function __construct() {
        parent::__construct();

        $this->load->helper('inflector');

        $this->_db = $this->db;

        if($this->_table_pre == null) {
            $this->_table_pre = $this->db()->dbprefix;
        }
    }

    final public function name() {
        if($this->_table_name == null) {
            $table_name = plural(preg_replace('/(_m|_model)?$/', '', strtolower(get_class($this))));
            $this->_table_name = $this->_table_pre . $table_name;
        }
        if(!empty($this->_table_alias)) {
            return $this->_table_name . ' AS ' . $this->_table_pre . $this->_table_alias;
        }

        return $this->_table_name;
    }

    final public function set_name($table_name) {
        $this->_table_name = $this->_table_pre . $table_name;
    }

    final public function set_table_alias($alias = '') {
        $this->_table_alias = $alias;

        return $this;
    }

    final public function pk() {
        return $this->_primary_key;
    }

    /**
     * @return CI_DB_pdo_driver|CI_DB_query_builder
     */
    final public function db() {
        return $this->_db;
    }

    final public function error() {
        if($this->_error) {
            return array('code' => '99999', 'message' => $this->_error);
        } else {
            return $this->db()->error();
        }
    }

    final public function error_message() {
        if($this->_error) {
            return $this->_error;
        } else {
            $error = $this->error();

            return $error['message'];
        }
    }

    final public function has_error() {
        if($this->_error) {
            return true;
        }

        $error = $this->error();
        $code = $error['code'];

        return $code && $code !== '00000';
    }

    final public function set_error($error) {
        $this->_error = $error;
    }


    /**
     * 设置select,支持as
     * array('user_id'=>'id') : select id as user_id
     * array('name','user_id'=>'id','sex') : select name,id as user_id,sex
     * @param string|array $select
     * @return $this|CI_DB_query_builder
     */
    final public function select($select = '*') {
        if(!$select) {
            return $this;
        }
        $table = $this->name();
        if(!empty($this->_table_alias)) {
            $table = $this->_table_alias;
        }
        if(!is_array($select)) {
            $select = explode(',', $select);
        }
        foreach($select as $k => $v) {
            if(strpos($v, '(') !== false) {
                $select[$k] = $v;
            } else {
                $select[$k] = $table . '.' . $this->db()->escape_identifiers($v);
            }
            if(!is_numeric($k)) {
                $select[$k] .= ' AS ' . $this->db()->escape_identifiers($k);
            }
        }
        $this->db()->select($select, false);

        return $this;
    }

    /**
     * 联合查询
     * @param    string $table
     * @param    string $cond the join condition
     * @param array $select 用法参照 Action_table::select()
     * @param    string $type the type of join
     * @param    string|null $escape whether not to try to escapowershellpe identifiers
     * @return    $this
     */
    final public function join($table, $cond, $select = array(), $type = '', $escape = null) {
        if(!is_array($select)) {
            $select = explode(',', $select);
        }
        $_tbname = preg_split('/\s/i', trim($table));
        $_tbname = array_pop($_tbname);
        foreach($select as $k => $v) {
            if(preg_match('/\s*\w+\s*\(\s*[\w\.]+\s*\)\s*/', $v)) {
                $select[$k] = $this->db()->escape_identifiers($v);
            } else {
                $select[$k] = $_tbname . '.' . $this->db()->escape_identifiers($v);
            }
            if(!is_numeric($k)) {
                $select[$k] .= ' AS ' . $this->db()->escape_identifiers($k);
            }
        }
        if($select) {
            $this->db()->select($select, false);
        }
        $this->db()->join($table, $cond, $type, $escape);

        return $this;
    }

    /**
     * 联合查询，方便Action_table
     * @param MY_Model $table
     * @param array $relation 数组，两个表的关系，key是table字段，value是this的字段
     * @param array $select 要查询的表的字段
     * @param string $type
     * @param null $escape
     */
    final public function table_join($table, $relation, $select = array(), $type = '', $escape = null) {
        $join_cond = array();
        foreach($relation as $k => $v) {
            $join_cond[] = $table->name() . '.' . $k . '=' . $this->name() . '.' . $v;
        }
        $this->join($table->name(), implode(' AND ', $join_cond), $select, $type, $escape);
    }

    /**
     * 查询记录是否存在
     * @param array $where 查询条件
     * @param bool $returnPk 是否返回记录主键(如果存在)
     * @return bool|mixed|null|string 存在则返回true或主键，否则返回NULL，sql错误返回false
     */
    final public function is_exists($where, $returnPk = false) {
        $record = $this->db()->where($where)->get($this->name(), 1);
        if($this->has_error()) {
            $this->reset_query();

            return false;
        }

        return $record->num_rows() ? ($returnPk ? array_value($record->row_array(), $this->pk()) : true) : null;
    }

    /**
     * 查询一条
     * @return array
     */
    final public function get() {
        $result = $this->db()->get($this->name(), 1);
        if($this->has_error()) {
            $this->reset_query();

            return array();
        }

        return $result->num_rows() ? $result->row_array() : array();
    }

    /**
     * 查询多条
     * @param null $limit
     * @param null $offset
     * @return array|array[]
     */
    final public function query($limit = null, $offset = null) {
        $table = $this->name();
        if($this->_re_query === true) {
            $table = '';
        }
        $result = $this->db()->get($table, $limit, $offset);
        if($this->has_error()) {
            $this->reset_query();

            return array();
        }

        return $result->num_rows() ? $result->result_array() : array();
    }

    /**
     * 查询并按指定的字段索引
     * @param $index_field_name
     * @param null $limit
     * @param null $offset
     * @return array
     * @throws Exception
     */
    final public function query_and_index($index_field_name, $limit = null, $offset = null) {
        $query = $this->query($limit, $offset);
        if(!$query) {
            return array();
        }
        if(!isset($query[0][$index_field_name])) {
            throw new Exception("指定键名:{$index_field_name}不在结果集里");
        }
        $rs = array();
        foreach($query as $row) {
            $rs[$row[$index_field_name]] = $row;
        }

        return $rs;
    }

    /**
     * 执行一条sql，返回一行结果
     * @param $sql
     * @return array
     */
    final public function get_sql($sql) {
        $result = $this->db()->query($sql);
        if($this->has_error()) {
            $this->reset_query();

            return array();
        }

        return $result->num_rows() ? $result->row_array() : array();
    }

    /**
     * 查询一条sql
     * @param $sql
     * @return array|array[]
     */
    final public function query_sql($sql) {
        $result = $this->db()->query($sql);
        if($this->has_error()) {
            $this->reset_query();

            return array();
        }

        return $result->num_rows() ? $result->result_array() : array();
    }

    /**
     * 执行一条写查询
     * @param $sql
     * @return bool
     */
    final public function exec_sql($sql) {
        return (bool)$this->db()->query($sql);
    }

    /**
     * 同查询多条 简化分页步骤 不再计算下标
     * @param null $limit
     * @param null $page
     * @return array|array[]
     */
    final public function to_query($limit = null, $page = null) {
        $offset = 0;
        $limit || !isset($_GET['page_size']) || $limit = (int)$_GET['page_size'];
        $page || !isset($_GET['page']) || $page = (int)$_GET['page'];
        $limit || $limit = 10;
        if($page)
            $offset = $limit * ($page - 1);
        $result = $this->db()->get($this->name(), $limit, $offset);
        if($this->has_error()) {
            $this->reset_query();

            return array();
        }

        return $result->num_rows() ? $result->result_array() : array();
    }

    /**
     * 执行查询并返回结果条数
     * @param bool $reset
     * @return int
     */
    final public function count_all_results($reset = true) {
        if($reset == false) {
            $this->_re_query = true;
        }
        $rs = (int)$this->db()->count_all_results($this->name(), $reset);
        if($this->has_error()) {
            $this->reset_query();
        }

        return $rs;
    }

    /**
     * 插入一条
     * @param null $set
     * @param null $escape
     * @return bool|int|object
     */
    final public function insert($set = null, $escape = null) {
        ($this->_created && !array_value($set, $this->_created)) && $set[$this->_created] = time();
        ($this->_updated && !array_value($set, $this->_updated)) && $set[$this->_updated] = time();
        $rs = $this->db()->insert($this->name(), $set, $escape);
        if($this->has_error()) {
            $this->reset_query();
        }

        return $rs ? $this->db()->insert_id() : $rs;
    }

    /**
     * 插入多条
     * @param null $set
     * @param null $escape
     * @param bool $ignore
     * @return bool|int
     */
    final public function insert_batch($set = null, $escape = null, $ignore = false) {
        if(empty($set)) {
            return false;
        }

        if($this->_created || $this->_updated) {
            $time = time();
            foreach($set as &$row) {
                ($this->_created && !array_value($row, $this->_created)) && $row[$this->_created] = $time;
                ($this->_updated && !array_value($row, $this->_updated)) && $row[$this->_updated] = $time;
            }
        }

        if($ignore) {
            $affected_rows = 0;
            for($i = 0, $total = sizeof($set); $i < $total; $i += 100) {
                $currentArray = array_slice($set, $i, 100);
                $query = '';
                foreach($currentArray as $row) {
                    if($escape) {
                        foreach($row as &$value) {
                            $value = $this->db()->escape($value);
                        }
                    }
                    $query .= ($query ? ',' : '') . '(' . implode(',', $row) . ')';
                }
                $query = 'INSERT IGNORE INTO `' . $this->name() . '` (`' . implode('`, `', array_keys($set[0])) . '`) VALUES ' . $query;
                $this->reset_query();
                $this->db()->simple_query($query);
                $affected_rows += $this->affected_rows();
            }

            return $affected_rows;
        }

        try {
            $rs = $this->db()->insert_batch($this->name(), $set, $escape);
        } catch(Exception $e) {
            $rs = false;
        }
        if($rs <= 0) {
            $this->set_error($this->db()->last_query() . ':插入条数不大于0');
            $rs = false;
        }
        if($this->has_error()) {
            $this->reset_query();
        }

        return $rs;
    }

    /**
     * 更新：返回影响条数或false，需要区分0和false
     * @param array $set An associative array of update values
     * @param mixed $where
     * @param int $limit
     * @return bool|int
     */
    final public function update($set = null, $where = null, $limit = null) {
        ($this->_updated && !array_value($set, $this->_updated)) && $set[$this->_updated] = time();
        $rs = $this->db()->update($this->name(), $set, $where, $limit);
        if($this->has_error()) {
            $this->reset_query();
        }

        return $rs !== false ? $this->db()->affected_rows() : false;
    }

    /**
     * 更新多条
     * @param array $set An associative array of update values
     * @param null $index
     * @return bool|int
     */
    final public function update_batch($set = null, $index = null) {
        if($this->_created || $this->_updated) {
            $time = time();
            foreach($set as &$row) {
                ($this->_updated && !array_value($row, $this->_updated)) && $row[$this->_updated] = $time;
            }
        }
        $rs = $this->db()->update_batch($this->name(), $set, $index);
        if($this->has_error()) {
            $this->reset_query();
        }

        return $rs !== false ? $this->db()->affected_rows() : false;
    }

    /**
     * Delete:返回影响条数或false，需要区分0和false
     * @param mixed $where the where clause
     * @param mixed $limit the limit clause
     * @param bool $reset_data
     * @return bool|int
     */
    final public function delete($where = '', $limit = null, $reset_data = true) {
        $rs = $this->db()->delete($this->name(), $where, $limit, $reset_data);
        if($this->has_error()) {
            $this->reset_query();
        }

        return $rs ? $this->db()->affected_rows() : false;
    }

    final public function replace($set) {
        $table = $this->name();

        return $this->db()->replace($table, $set);
    }

    /**
     * 保存一条记录(不存在插入，存在则更新)
     * @param $params
     * @param $unique_params
     * @return bool|int|mixed|null|object|string
     */
    final public function save($params, $unique_params) {
        $result = $this->is_exists($unique_params, true);
        if($result === false) {
            return false;
        }
        if($result === null) {
            return $this->insert($params);
        }
        $this->update($params, $unique_params);

        return $result;
    }

    protected $_set_method = array('select_max' => 1, 'select_min' => 1, 'select_avg' => 1, 'select_sum' => 1, 'distinct' => 1, 'where' => 1, 'or_where' => 1, 'where_in' => 1, 'or_where_in' => 1, 'where_not_in' => 1, 'or_where_not_in' => 1, 'like' => 1, 'not_like' => 1, 'or_like' => 1, 'or_not_like' => 1, 'group_start' => 1, 'or_group_start' => 1, 'not_group_start' => 1, 'or_not_group_start' => 1, 'group_end' => 1, 'group_by' => 1, 'having' => 1, 'or_having' => 1, 'order_by' => 1, 'limit' => 1, 'offset' => 1, 'set' => 1, 'reset_query' => 1, 'trans_start' => 1, 'trans_complete' => 1, 'trans_rollback' => 1,);

    public function __call($method, $params) {
        if(isset($this->_set_method[$method])) {
            call_user_func_array(array($this->db(), $method), $params);

            return $this;
        }
        in_development() ? var_dump("{$method}未被支持") : null;
        throw new Exception("{$method}未被支持");
    }

    /**
     * 返回最后插入id
     * @return int
     */
    public function insert_id() {
        return $this->db()->insert_id();
    }

    /**
     * 返回影响条数
     * @return int
     */
    public function affected_rows() {
        return $this->db()->affected_rows();
    }

    /**
     * 生成select sql
     * @return string
     */
    public function get_select_sql() {
        return $this->db()->get_compiled_select($this->name());
    }

    /**
     * 生成insert sql
     * @return string
     */
    public function get_insert_sql() {
        return $this->db()->get_compiled_insert($this->name());
    }

    /**
     * 生成update sql
     * @return string
     */
    public function get_update_sql() {
        return $this->db()->get_compiled_update($this->name());
    }

    /**
     * 生成delete sql
     * @return string
     */
    public function get_delete_sql() {
        return $this->db()->get_compiled_delete($this->name());
    }

    /**
     * 获取最后一条运行的SQL
     * @return string
     */
    public function get_last_query() {
        return $this->db()->last_query();
    }

    public function reset_query() {
        $this->_re_query = false;
        $this->_table_alias = null;
        $this->db()->reset_query();

        return $this;
    }

    public function set_updated_at() {
        $this->_updated = '';
    }
}
