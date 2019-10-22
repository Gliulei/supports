<?php
/**
 * @since  2019-10-22
 */

namespace App\Supports;


class Query {

    private $request;

    private $build;

    public function __construct()
    {
        $this->request = new Request();
    }

    /**
     * 封装query查询
     * @param        $key
     * @param string $format
     * @param string $defaultValue
     * @return $this
     * @author liu.lei
     */
    public function query($key, $format = 'string', $defaultValue = '')
    {
        $val = $this->request->input($key, $defaultValue, false);
        if ($val) {
            switch ($format) {
                case 'int':
                    $this->build[$key] = intval($val);
                    break;
                case 'string':
                    $this->build[$key] = (string)($val);
                    break;
                case 'gte':
                    $this->build[$key]['$gte'] = strtotime($val);
                    break;
                case 'lte':
                    $this->build[$key]['$lte'] = strtotime($val);
                    break;
                case 'regex':
                    $this->build[$key] = ['$regex' => $val];
                    break;

            }
        }

        return $this;
    }

    /**
     * 获取查询语句
     * @return mixed
     * @author liu.lei
     */
    public function build()
    {
        return $this->build;
    }

}