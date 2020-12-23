<?php
/**
 * Created by PhpStorm.
 * @file   ApiSpiderCommand.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2020/12/18 下午4:53
 * @desc   ApiSpiderCommand.php
 */

namespace Generate\Command;

use Illuminate\Support\Facades\DB;
use core\utils\HttpUtils;
use Generate\Common\GenerateCommand;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApiSpiderCommand extends GenerateCommand
{

    protected $name = "generate:spider";
    protected $description = "抓取API列表";

    protected $baseUri = 'http://yapi.vhall.domain/api/project/list?group_id=505&page=1&limit=10';
//    protected $baseUri = 'http://yapi.vhall.domain/project/104/interface/api';


    /**
     * @param $uri
     * @return \Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function fetch($uri)
    {
        $headers = [
            'cookie' => "_yapi_token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1aWQiOjgxOSwiaWF0IjoxNjA4MjgxMjcyLCJleHAiOjE2MDg4ODYwNzJ9.1oRXPjAv_Qq6eb1-DNO2saEa1bQaaOyYhHIoujs6N0E; _yapi_uid=819"
        ];
        $client = new Client();
        $response = $client->request("get", $uri, [
            RequestOptions::HEADERS => $headers
        ]);
        $body = $response->getBody();
        return \GuzzleHttp\json_decode($body, true);
    }

    private function getApiList($categoryId, $page)
    {
        $uri = "http://yapi.vhall.domain/api/interface/list?page=%s&limit=20&project_id=%s";
        echo $uri = sprintf($uri, $page, $categoryId);
        echo PHP_EOL;
        return $this->fetch($uri);
    }

    public function getCategory($categoryId)
    {
        $uri = "http://yapi.vhall.domain/api/project/get?id=%s";
        $uri = sprintf($uri, $categoryId);
        return $this->fetch($uri);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(InputInterface $input, OutputInterface $output)
    {
        $response = $this->fetch($this->baseUri);
        $apiList = [];
        foreach ($response['data']['list'] as $category) {
            echo $category['name'], PHP_EOL;
            //获取分类信息
            $categoryList = $this->getCategory($category['_id']);
            $categoryList = collect($categoryList['data']['cat'])->keyBy("_id")->map(function ($item) {
                return $item['name'];
            })->toArray();
            $page = 1;
            while (true) {
                $result = $this->getApiList($category['_id'], $page++);
                if (count($result['data']['list']) > 1) {
                    $apiList[] = $result['data']['list'];
                    collect($result['data']['list'])->each(function ($item, $key) use ($category, $categoryList) {
                        $sql = "insert into rbac_permissions set category_ids='2,3,4,5,6,7,8,9,10,11,12,13',
                                 permission_name='[{$category['name']}] [{$categoryList[$item['catid']]}]  {$item["title"]}',
                                 permission_key='{$item["path"]}',
                                 operator_user_id=0,
                                 permission_type=0,
                                 operator_user_name='robot',
                                 remark='method:{$item['method']}|status:{$item['status']}'
                                 ";
                        $this->line("[{$key}]  insert 接口：[{$item['title']}]");
                        DB::connection()->insert($sql);
                    });
                } else {
                    break;
                }

            }
        }
        $json = collect($apiList)->flatten(1)->values()->toJson();
        $file = "/Volumes/development/web/saas-user/zz.json";
        file_put_contents($file, $json);
    }
}
