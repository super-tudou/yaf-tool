<?php
/**
 * Created by PhpStorm.
 * @file   ControllerCommand.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2020/11/10 4:51 下午
 * @desc   ControllerCommand.php
 */

namespace Generate\Command;


use Generate\Common\GenerateCommand;
use Generate\Services\CodeService;
use Generate\Services\ConfigService;
use Generate\Services\FileService;
use Generate\Services\TemplateService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ControllerCommand extends GenerateCommand
{

    protected $name = "generate:controller";

    protected $description = "自动生成控制器脚本";

    protected $template = 'ControllerTemplate.tmp';

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function handle(InputInterface $input, OutputInterface $output)
    {

        $tableName = $this->selectTable($input);

        $this->info("[*] start to get table info!");
        $controllerName = CodeService::getInstance()->getClassName($tableName);
        $comment = $this->tableService->getTableComment($tableName);

        $listParamsFilter = $infoParamsFilter = [];
        $listSearch = $input->getArgument('list:search');
        if (!empty($listSearch)) {
            $listParamsFilter = collect($listSearch)->map(function ($item) {
                return "            '{$item}' => \$params['{$item}']";
            })->toArray();
        }
        $infoSearch = $input->getArgument('info:search');
        if (!empty($infoSearch)) {
            $infoParamsFilter = collect($infoSearch)->map(function ($item) {
                return "            '{$item}' => \$params['{$item}']";
            })->toArray();
        }

        $tableFields = $this->tableService->getFields($tableName);
        $createFields = collect($tableFields)->map(function ($item) {
            $item = (array)$item;
            if (!in_array($item['column_name'], ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                return "            '{$item['column_name']}' => \$params['{$item['column_name']}']";
            }
        })->toArray();

        $updateField = collect($tableFields)->map(function ($item) {
            $item = (array)$item;
            if (!in_array($item['column_name'], ['id','created_at', 'updated_at', 'deleted_at'])) {
                return "        isset(\$params['{$item['column_name']}']) && \$update['{$item['column_name']}'] = \$params['{$item['column_name']}'];";
            }
        })->toArray();

        $editFields=["            'id' => \$params['id']"];

        $deleteFields = ["            'id' => \$params['id']"];
        $table = CodeService::getInstance()->getClassName($tableName);
        $params = [
            'controllerName' => $controllerName,
            'date' => date("Y-m-d H:i:s"),
            'desc' => $comment,
            'ruleName' => CodeService::getInstance()->getClassName($tableName, 'Rule'),
            'transName' => CodeService::getInstance()->getClassName($tableName, 'Trans'),
            'serviceName' => CodeService::getInstance()->getClassName($tableName, 'Service'),
            'responseName' => CodeService::getInstance()->getClassName($tableName, 'Response'),
            'controller' => CodeService::getInstance()->getClassName($tableName, 'Controller'),
            'table' => $table,
            'listParamsFilter' => join(",\n", array_filter($listParamsFilter)),
            'infoParamsFilter' => join(",\n", array_filter($infoParamsFilter)),
            'createFields' => join(",\n", array_filter($createFields)),
            'editFields' => join(",\n", array_filter($editFields)),
            'deleteFields' => join(",\n", array_filter($deleteFields)),
            'listKey' => strtoupper($table),
            'updateField' => join("\n", array_filter($updateField)),
        ];

        $this->info("[*] start to get controller code!");
        $code = TemplateService::getInstance()->getCode($this->template, $params);
        $file = ConfigService::getModuleName() . 'controllers/' . $controllerName . ".php";
        $this->info("[*] start to save controller file!");
        FileService::getInstance()->saveCode($file, $code);
        $this->info("[*] save sevice[{$controllerName}] success!");
    }
}
