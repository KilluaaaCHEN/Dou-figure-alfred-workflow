<?php

/**
 *
 * Class Base
 * @author Killua Chen
 */
class Base
{
    //图片保存地址
    protected $store_path;

    //图片匹配规则
    protected $img_match = '/data-original\=\"\/\/([\s\S]*?)\"/';

    //名称匹配规则
    protected $name_match = '/<p style=\"([\s\S]*?)<\/p>/';

    //检索网址
    protected $search_url = 'https://www.doutula.com/search?keyword=';

    //列表页网址
    protected $list_url = 'https://www.doutula.com/photo/list/?page=';

    //检索历史路径
    protected $cache_path = 'cache.txt';

    function __construct()
    {
        $this->store_path = '/Users/' . explode('/', __DIR__)[2] . '/Pictures/.DouTu/';
    }

    /**
     * get请求
     * @param $url
     * @return mixed
     */
    public function get($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        return curl_exec($ch);
    }


    /**
     * 获取图片路径,并检测下载
     * @param $img
     * @param bool $is_download
     * @return string
     * @author Killua Chen
     */
    public function getImgPath($img, $is_download = false)
    {
        $file_path = $this->store_path . md5($img) . '.png';
        if ($is_download && !file_exists($file_path)) {
            $file = $this->get($img);
            file_put_contents($file_path, $file);
        }
        return $file_path;
    }

    /**
     * 检测图片存储路径
     * @author Killua Chen
     */
    public function checkStorePath()
    {
        if (!file_exists($this->store_path)) {
            mkdir($this->store_path);
        }
    }


}

