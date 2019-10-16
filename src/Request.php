<?php

/**
 * @since  2019-10-16
 */
namespace App\Supports;

/**
 * Class Request
 *
 * @package App\Supports
 */

class Request
{

    /**
     * 记录request参数，包含从url rewrite解析出来的参数
     *
     * @var array
     */
    private $params = [];

    /**
     * @var Response
     */
    private $response;

    public function __construct()
    {
        $this->initParams();
        $this->response = new Response();
    }

    public function initParams()
    {
        foreach ($_REQUEST as $key => $value) {
            $this->params[$key] = $value;
        }
    }

    /**
     * @param      $name
     * @param null $default
     * @return null
     */
    public function cookie($name, $default = null)
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
    }

    /**
     * @param      $name
     * @param null $default
     * @return null
     */
    public function post($name, $default = null)
    {
        return isset($_POST[$name]) ? $_POST[$name] : $default;
    }

    /**
     * 增加支持从header 中取值
     *
     * @param      $name
     * @param null $default
     * @return null
     */
    public function header($name, $default = null)
    {
        $hKey = 'HTTP_' . str_replace('-', '_', strtoupper($name));

        return isset($_SERVER[$hKey]) ? $_SERVER[$hKey] : $default;
    }

    /**
     * @param     $name
     * @param int $default
     * @return int
     */
    public function int($name, $default = 0)
    {
        return isset($this->params[$name]) ? intval($this->params[$name]) : $default;
    }

    /**
     * @param       $name
     * @param float $default
     * @return float
     */
    public function float($name, $default = 0.0)
    {
        return isset($this->params[$name]) ? floatval($this->params[$name]) : $default;
    }

    /**
     * @param        $name
     * @param string $default
     * @param int    $len
     * @param string $encode
     * @return null|string
     */
    public function string($name, $default = '', $len = 0, $encode = 'utf8')
    {
        $string = $this->get($name, $default);
        if (!is_string($string)) {
            return '';
        }
        if ($len) {
            $string = mb_substr($string, 0, $len, $encode);
        }

        return strval($string);
    }

    /**
     * @param        $name
     * @param string $charlist
     * @param string $default
     * @param int    $len
     * @param string $encode
     * @return string
     */
    public function trim($name, $charlist = " \t\n\r\0\x0B", $default = '', $len = 0, $encode = 'utf8')
    {
        return trim($this->string($name, $default, $len, $encode), $charlist);
    }

    /**
     * @param      $name
     * @param null $default
     * @return null
     */
    public function get($name, $default = null)
    {
        return isset($this->params[$name]) ? $this->params[$name] : $default;
    }

    public function params()
    {
        return $this->params;
    }

    public function ip()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    public function isAjax() {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取输入的数据，兼容 delete，put，patch 带有验证的方式
     *
     * @param null  $key
     * @param null  $default
     * @param mixed $validation
     * @param mixed $value
     * @return array|mixed|null
     */
    public function input($key = null, $value = '', $validation = '',  $default = null) {
        $input = $this->getInputSource() + $this->params();
        if (is_null($key)) {
            return $input;
        }

        $val = isset($input[$key]) ? $input[$key] : $default;
        if ($validation !== false) {
            if ($validation instanceof \Closure) {
                $validation();
            } elseif (empty($val)) {
                $value = $value ? $value : $key;
                $this->response->error(400, $value . '字段不能为空!');
                $this->response->send();
            }
        }

        return $val;
    }

    /**
     * Get the JSON payload for the request.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function json($key = null, $default = null) {
        $json = (array)json_decode($this->getContent(), true);

        if (is_null($key)) {
            return $json;
        } else {
            return isset($json[$key]) ? $json[$key] : $default;
        }
    }

    /**
     * Determine if the request is sending JSON.
     *
     * @return bool
     */
    public function isJson() {
        foreach (['/json', '+json'] as $needle) {
            if (mb_strpos($_SERVER['CONTENT_TYPE'], $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the input source for the request.
     *
     * @return array
     */
    protected function getInputSource() {
        if ($this->isJson()) {
            return $this->json();
        }
        $data = [];
        if (in_array($this->getMethod(), ['GET', 'HEAD'])) {
            $data = $this->params();
        } else {
            if (0 === strpos($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded')
                && in_array($this->getMethod(), ['PUT', 'DELETE', 'PATCH'])
            ) {
                parse_str($this->getContent(), $data);
            }
        }

        return $data;
    }

    /**
     * get request method
     *
     * @return string
     */
    public function getMethod() {
        return strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');
    }

    /**
     * get content
     *
     * @return bool|string
     */
    public function getContent() {
        return file_get_contents('php://input');
    }
}