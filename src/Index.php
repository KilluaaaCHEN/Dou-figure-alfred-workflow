<?php

/**
 * 斗图神器
 * Class Index
 * @author Killua Chen
 */
require 'Base.php';

class Index extends Base
{

    /**
     * 检索图片
     * @param $query
     * @author Killua Chen
     */
    public function search($query)
    {
        require_once 'workflows.php';
        $w = new Workflows();
        $content = $this->get($this->search_url . $query);
        preg_match_all($this->img_match, $content, $img_list);
        foreach ($img_list[1] as $i => $item) {
            if ($i < 9) {
                $this->getImgPath($item, true);
            }
        }
        preg_match_all($this->name_match, $content, $name_list);
        foreach ($name_list[1] as &$item) {
            $item = substr($item, strpos($item, '>') + 1);
        }

        foreach ($img_list[1] as $i => $img) {
            $img_path = $this->getImgPath($img, false);
            $w->result(time(), $img, $name_list[1][$i], '', $img_path, 'yes');
        }
        if (count($w->results()) == 0) {
            $w->result(time(), '', 'Not Found', 'Are you sure for "' . $query . '" ???', 'icon.png', 'no');
        }

        if ($fp = fopen($this->cache_path, 'a+')) {
            $startTime = microtime();
            do {
                $canWrite = flock($fp, LOCK_EX);
                if (!$canWrite) {
                    usleep(round(rand(0, 100) * 1000));
                }
            } while ((!$canWrite) && ((microtime() - $startTime) < 1000));
            if ($canWrite) {
                fwrite($fp, "$query\n");
            }
            fclose($fp);
        }

        echo $w->toxml();
    }

    public function first($query)
    {
        require_once 'workflows.php';
        $w = new Workflows();
        $w->result(time(), $query, "Search 斗图啦 for '{$query}'", '键入enter开始检索', 'icon.png', 'yes');
        echo $w->toxml();
    }

    /**
     * 预览图片
     * @param $query
     * @author Killua Chen
     */
    public function preview($query)
    {
        $img_path = $this->getImgPath($query, true);
        echo $img_path;
    }

    /**
     * 下载检索历史所有图片
     * @author Killua Chen
     */
    public function download()
    {
        $lines = file_get_contents($this->cache_path);
        unlink($this->cache_path);
        $query_list = array_filter(array_unique(explode("\n", $lines)));
        foreach ($query_list as $query) {
            $content = $this->get($this->search_url . $query);
            preg_match_all($this->img_match, $content, $img_list);
            foreach ($img_list[1] as $img) {
                $this->getImgPath($img, true);
            }
        }

    }


    /**
     * 下载所有图片
     * @param int $start
     * @author Killua Chen
     */
    public function downloadAll($start = 1)
    {
        $this->checkStorePath();
        $is_cs = false;//是否重试
        for ($i = $start; $i < $start + 100; $i++) {
            $content = $this->get($this->list_url . $i);
            preg_match_all($this->img_match, $content, $img_list);
            if (count($img_list[1]) < 10) {
                if (!$is_cs) {
                    $i--;
                    continue;
                }
                break;
            }
            $is_cs = false;
            foreach ($img_list[1] as $img) {
                $this->getImgPath($img, true);
            }
        }
    }

}

$index = new Index();
@$index->{$argv[1]}($argv[2]);
