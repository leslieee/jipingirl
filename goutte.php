<?php
use Goutte\Client;
require "vendor/autoload.php";
date_default_timezone_set("Asia/Shanghai");

$client = new Client();
$client->setHeader('user-agent', "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.2272.101 Safari/537.36");
// 拉取列表
for ($i=1; $i<=1; $i++) {
    $pageUrl = "https://mrcong.com/category/nguoi-dep/page/" . $i;
    $crawler = $client->request('GET', $pageUrl);
    $listArray = [];
    $crawler->filter('.post-box-title > a')->each(function ($node) {
        global $listArray;
        array_push($listArray,$node->attr("href"));
    });
    if (count($listArray) > 0) {
        foreach ($listArray as $list) {
            // 先把6页的img链接汇聚在一起
            $crawler = $client->request('GET', $list);
            $linkArray = [];
            // 第一页的数据
            $pageone = "";
            $crawler->filter('p > img')->each(function ($node) {
                global $pageone;
                $pageone = $pageone . "<a target=\"_blank\" href=\"" . $node->attr("src") . "\"><img src=\"" . $node->attr("src") . "\"></a>" . "\n";
                echo "<a target=\"_blank\" href=\"" . $node->attr("src") . "\"><img src=\"" . $node->attr("src") . "\"></a>" . "\n";
            });
            // 翻页数据
            $crawler->filter('.page-link > a')->each(function ($node) {
                // 数组保存并去重
                global $linkArray;
                array_push($linkArray,$node->attr("href"));
            });
            if (count($linkArray) > 0) {
                $linkArray = array_unique($linkArray);
                $linkArray = array_values($linkArray);
                foreach ($linkArray as $url) {
                    $crawler = $client->request('GET', $url);
                    $crawler->filter('p > img')->each(function ($node) {
                        global $pageone;
                        $pageone = $pageone . "<a target=\"_blank\" href=\"" . $node->attr("src") . "\"><img src=\"" . $node->attr("src") . "\"></a>" . "\n";
                        echo "<a target=\"_blank\" href=\"" . $node->attr("src") . "\"><img src=\"" . $node->attr("src") . "\"></a>" . "\n";
                    });
                }
            }
            $filename = "./data/jipingirl_" .date("Ymd") . ".html";
            $file = fopen($filename, "a");
            fwrite($file, $pageone);
            fclose($file);
        }
    }
}


// 更新主页
$filename = "./data/jipingirl_" .date("Ymd") . ".html";
$count = countLine($filename);
$insertContent = "<a href=\"http://ccccccc.cf/data/" . $filename . "\">" . date("Ymd") . " 更新 " . $count . "张</a><br>";
insertAfterTarget("index.html", $insertContent, "<!--insert-->");

// 统计行数
function countLine($file) {
    $fp=fopen($file, "r");
    $i=0;
    while(!feof($fp)) {
        //每次读取2M
        if($data=fread($fp,1024*1024*2)){
            //计算读取到的行数
            $num=substr_count($data,"\n");
            $i+=$num;
        }
    }
    fclose($fp);
    return $i;
}

#在需要查找的内容后一行新起一行插入内容
function insertAfterTarget($filePath, $insertCont, $target)
{
    $result = null;
    $fileCont = file_get_contents($filePath);
    $targetIndex = strpos($fileCont, $target); #查找目标字符串的坐标

    if ($targetIndex !== false) {
        #找到target的后一个换行符
        $chLineIndex = strpos(substr($fileCont, $targetIndex), "\n") + $targetIndex;
        if ($chLineIndex !== false) {
            #插入需要插入的内容
            $result = substr($fileCont, 0, $chLineIndex + 1) . $insertCont . "\n" . substr($fileCont, $chLineIndex + 1);
            $fp = fopen($filePath, "w+");
            fwrite($fp, $result);
            fclose($fp);
        }
    }
}