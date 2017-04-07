<?php

/**
 * 下载检索过的所有图片
 * Class Index
 */
class Download
{
    public function down()
    {
        $lines = file_get_contents('cache.txt');
        $str_list = explode("\n", $lines);
        $str_list = array_unique($str_list);

        require 'config.php';

        foreach ($str_list as $query) {
            if (trim($query)) {
                $content = $this->get('https://www.doutula.com/search?keyword=' . $query);
                preg_match_all('/data-original\=\"\/\/([\s\S]*?)\"/', $content, $img_list);
                foreach ($img_list[1] as $img) {
                    $file_path = $store_path . md5($img) . '.png';
                    if (!file_exists($file_path)) {
                        $file = $this->get($img);
                        file_put_contents($file_path, $file);
                    }
                }
            }
        }
        file_put_contents('cache.txt', '');
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

(new Download())->down();
exit;