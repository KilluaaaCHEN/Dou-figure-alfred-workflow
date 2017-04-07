<?php

/**
 * 斗图神器
 * Class Index
 */
class Preview
{
    public function search($query)
    {
        $file_path = __DIR__ . '/tmp/' . md5($query) . '.png';
        if (!file_exists($file_path)) {
            $file = $this->get($query);
            file_put_contents($file_path, $file);
        }

        echo $file_path;
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

}

(new Preview())->search($argv[1]);
exit;