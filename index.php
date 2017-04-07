<?php

/**
 * 斗图神器
 * Class Index
 */
class Index
{
    public function search($query)
    {
        $content = $this->get('https://www.doutula.com/search?keyword=' . $query);
        preg_match_all('/data-original\=\"\/\/([\s\S]*?)\"/', $content, $img_list);
        foreach ($img_list[1] as $i => $item) {
            if ($i < 10) {
                $file_path = __DIR__ . '/tmp/' . md5($item) . '.png';
                if (!file_exists($file_path)) {
                    $file = $this->get($item);
                    file_put_contents($file_path, $file);
                }
            }
        }
        preg_match_all('/<p style=\"([\s\S]*?)<\/p>/', $content, $name_list);
        foreach ($name_list[1] as &$item) {
            $item = substr($item, strpos($item, '>') + 1);
        }
        require('workflows.php');
        $w = new Workflows();
        foreach ($img_list[1] as $i => $img) {
            $img_path = __DIR__ . '/tmp/' . md5($img) . '.png';
            $w->result(time(), $img, $name_list[1][$i], '', $img_path, 'yes');
        }
        $handle = fopen('cache.txt', "a+");
        $str = fwrite($handle, "$query\n");
        fclose($handle);

        echo $w->toxml();
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

if (!isset($argv[1])) {
    die('请输入斗图表情关键字');
}
(new Index())->search($argv[1]);
exit;