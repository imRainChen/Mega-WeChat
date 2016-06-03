<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/3/15
 * Time: 11:52
 */

namespace Wechat;


class Template
{
    private $openid;
    private $data;
    private $template;

    /**
     * @return mixed
     */
    public function getOpenid()
    {
        return $this->openid;
    }

    /**
     * @param mixed $openid
     */
    public function setOpenid($openid)
    {
        if (!is_string($openid)) {
            throw new \InvalidArgumentException('openid must be string');
        }
        $this->openid = $openid;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        if (!is_array($data) && $data !== null) {
            throw new \InvalidArgumentException('data must be array');
        }
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param mixed $template
     */
    public function setTemplate($template)
    {
        if (!is_string($template)) {
            throw new \InvalidArgumentException('template must be json string');
        }
        $this->template = $template;
    }

    /**
     * 解析template数据
     * @return string
     */
    public function parse()
    {
        $pattern = [
            '/\\$\\{OPENID\\}/',
        ];

        $replacement = [
            $this->openid,
        ];

        if ($this->data !== null) {
            foreach($this->data as $key => $value)
            {
                $pattern[] = "/\\$\\{{$key}\\}/";
                $replacement[] = $value;
            }
        }

        $result = preg_replace($pattern, $replacement, $this->template);

        return $result;
    }
}