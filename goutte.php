<?php
use Goutte\Client;
require "vendor/autoload.php";

$client = new Client();
$client->setHeader('user-agent', "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.2272.101 Safari/537.36");
// 拉取列表
for ($i=1; $i<=406; $i++) {
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
            $file = fopen("jipingirl.html", "a");
            fwrite($file, $pageone);
            fclose($file);
        }
    }
}
