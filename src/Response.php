<?php


namespace App\Supports;

/**
 * Class Response
 *
 * @package App\Supports
 */
class Response
{

    protected $ctx;
    protected $code;
    protected $message;
    protected $trace;
    protected $status;
    protected $debug;
    protected $data = [];

    public function __construct()
    {
    }

    public function success($data = [], $code = 200, $message = 'success')
    {
        $this->data    = $data;
        $this->code    = $code;
        $this->message = $message;
        $this->status  = 'success';
        $origin_url    = $_SERVER['REQUEST_URI'] ?? '';

        if (!empty($origin_url)) {
            $_SERVER['REQUEST_URI'] = $origin_url;
        }

        return $this;
    }

    public function error($code = '500', $message = '服务器出现错误，请稍后重试', $trace = '')
    {
        $this->code    = $code;
        $this->message = $message;
        $this->trace   = $trace;
        $this->status  = 'error';
        $origin_url    = $_SERVER['REQUEST_URI'] ?? '';
        if (!empty($origin_url)) {
            $_SERVER['REQUEST_URI'] = $origin_url;
        }

        return $this;
    }

    public function debug()
    {
        $this->debug = true;

        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function send()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache');

        $time_float = microtime(true);
        $cost_time  = $time_float - $_SERVER['REQUEST_TIME_FLOAT'];
        $wt         = (float)sprintf('%5.3f', $cost_time);
        $result     = [
            'ec' => $this->code,
            'em' => $this->message,
            'wt' => $wt
        ];
        if ($this->status === 'success' || !empty($this->data)) {
            $result['data'] = $this->data;
        }
        if ($this->status === 'error' && $this->trace  && $this->debug) {
            $result['debug'] = $this->trace;
            exit(json_encode($result, JSON_UNESCAPED_UNICODE));
        }
        exit(json_encode($result));
    }

}