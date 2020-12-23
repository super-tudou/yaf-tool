<?php
/**
 * Created by PhpStorm.
 * @file   ResponseCommand.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2020/11/10 5:24 下午
 * @desc   ResponseCommand.php
 */

namespace Generate\Command;


use Generate\Common\GenerateCommand;
use Generate\Services\CodeService;
use Generate\Services\ConfigService;
use Generate\Services\FileService;
use Generate\Services\TemplateService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResponseCommand extends GenerateCommand
{

    protected $name = "generate:response";

    protected $description = "自动生成配置脚本";

    protected $template = 'ResponseTemplate.tmp';

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function handle(InputInterface $input, OutputInterface $output)
    {
        $tableName = $this->selectTable($input);

        $this->info("[*] start to create response design .....");
        $this->info("[*] start to get table info!");
        $responseName = CodeService::getInstance()->getClassName($tableName, 'Response');
        $comment = $this->tableService->getTableComment($tableName);
        $tableFields = $this->tableService->getFields($tableName);
        $fields = collect($tableFields)->map(function ($item) {
            $item = (array)$item;
            return "'{$item['column_name']}'";
        })->toArray();
        $table = CodeService::getInstance()->getClassName($tableName);
        $infoResponse = $listRule = $this->tableService->getTableFieldDesign($tableFields);
        $dataResponse = collect($infoResponse)->map(function ($item) {
            $item = (array)$item;
            if ($item['type'] == 'integer') {
                return "            '{$item['field']}'=>['type' => 'intval', 'is_must' => 1, 'default' => '0']";
            } else {
                return "            '{$item['field']}'=>['type' => 'strval', 'is_must' => 1, 'default' => '', 'function' => ['trim']]";
            }
        })->toArray();
        $params = [
            'response' => $responseName,
            'date' => date("Y-m-d H:i:s"),
            'desc' => $comment,
            'fields' => join(", ", $fields),
            'table' => strtoupper($table),
            'dataResponse' => join(",\n", $dataResponse),
        ];
        $this->info("[*] start to get response code!");
        $code = TemplateService::getInstance()->getCode($this->template, $params);
        $file = ConfigService::getModuleName() . 'responses/' . $responseName . ".php";
        $this->info("[*] start to save response file!");
        FileService::getInstance()->saveCode($file, $code);
        $this->info("[*] save response[{$responseName}] success!");
    }

}
