<?php

/**
 * 下载所有图片
 * Class Index
 */
class DownloadAll
{
    public function down($start = 1)
    {
        $url = 'https://www.doutula.com/photo/list/?page=';

        require 'config.php';

        if (!file_exists($store_path)) {
            mkdir($store_path);
        }

        $is_cs = false;//是否重试

        for ($i = $start; ; $i++) {
            $content = $this->get($url . $i);
            preg_match_all('/data-original\=\"\/\/([\s\S]*?)\"/', $content, $img_list);
            if (count($img_list[1]) < 10) {
                if (!$is_cs) {
                    $i--;
                    continue;
                }
                break;
            }
            $is_cs = false;
            foreach ($img_list[1] as $img) {
                $file_path = $store_path . md5($img) . '.png';
                if (!file_exists($file_path)) {
                    $file = $this->get($img);
                    file_put_contents($file_path, $file);
                }
            }
        }
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

$start = intval($argv[1]);
(new DownloadAll())->down($start);
exit;