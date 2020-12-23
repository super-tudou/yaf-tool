<?php
/**
 * Created by PhpStorm.
 * @file   TransCommand.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2020/11/10 5:20 下午
 * @desc   TransCommand.php
 */

namespace Generate\Command;


use Generate\Common\GenerateCommand;
use Generate\Services\CodeService;
use Generate\Services\ConfigService;
use Generate\Services\FileService;
use Generate\Services\TemplateService;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TransCommand extends GenerateCommand
{


    protected $name = "generate:trans";

    protected $description = "自动生成转换脚本";

    protected $template = 'TransTemplate.tmp';

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    public function handle(InputInterface $input, OutputInterface $output)
    {
        $tableName = $this->selectTable($input);

        $this->info("[*] start to create model .....");
        $this->info("[*] start to get table info!");
        $transName = CodeService::getInstance()->getClassName($tableName, 'Trans');
        $responseName = CodeService::getInstance()->getClassName($tableName, 'Response');
        $comment = $this->tableService->getTableComment($tableName);
        $tableFields = $this->tableService->getFields($tableName);
        $infoResponse = $listRule = $this->tableService->getTableFieldDesign($tableFields);
        $fields = collect($infoResponse)->map(function ($item) {
            $item = (array)$item;
            if ($item['type'] == 'integer') {
                return "         '{$item['field']}'=>['type' => 'intval', 'is_must' => 1, 'default' => '0']";
            } else {
                return "          '{$item['field']}'=>['type' => 'strval', 'is_must' => 1, 'default' => '', 'function' => ['trim']]";
            }
        })->toArray();
        $params = [
            'trans' => $transName,
            'date' => date("Y-m-d H:i:s"),
            'desc' => $comment,
            'fields' => join(",\n", $fields),
            'table' => $tableName,
            'response' => $responseName
        ];
        $this->info("[*] start to get trans code!");
        $code = TemplateService::getInstance()->getCode($this->template, $params);
        $file = ConfigService::getModuleName() . 'translations/' . $transName . ".php";
        $this->info("[*] start to save trans file!");
        FileService::getInstance()->saveCode($file, $code);
        $this->info("[*] save model[{$transName}] success!");
    }
}
