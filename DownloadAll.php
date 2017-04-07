<?php

/**
 * 下载所有图片
 * Class Index
 */
class DownloadAll
{
    public function down()
    {
        $url = 'https://www.doutula.com/photo/list/?page=';

        require 'config.php';

        for ($i = 1; ; $i++) {
            $content = $this->get($url . $i);
            preg_match_all('/data-original\=\"\/\/([\s\S]*?)\"/', $content, $img_list);
            if (count($img_list[1]) < 10) {
                break;
            }
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

(new DownloadAll())->down();
exit;