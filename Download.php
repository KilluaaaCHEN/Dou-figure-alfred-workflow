<?php

/**
 * 斗图神器
 * Class Index
 */
class Download
{
    public function down()
    {
        $query = file_get_contents('cache.txt');
        $content = $this->get('https://www.doutula.com/search?keyword=' . $query);
        preg_match_all('/data-original\=\"\/\/([\s\S]*?)\"/', $content, $img_list);
        foreach ($img_list[1] as $item) {
            $file_path = __DIR__ . '/tmp/' . md5($item) . '.png';
            if (!file_exists($file_path)) {
                $file = $this->get($item);
                file_put_contents($file_path, $file);
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

(new Download())->down();
exit;