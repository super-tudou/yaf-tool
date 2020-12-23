<?php
/**
 * Created by PhpStorm.
 * @file   ServiceCommand.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2020/11/10 5:41 下午
 * @desc   ServiceCommand.php
 */

namespace Generate\Command;


use Generate\Common\GenerateCommand;
use Generate\Services\CodeService;
use Generate\Services\ConfigService;
use Generate\Services\FileService;
use Generate\Services\TemplateService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServiceCommand extends GenerateCommand
{

    protected $name = "generate:service";

    protected $description = "自动生成配置脚本";

    protected $template = 'ServiceTemplate.tmp';

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function handle(InputInterface $input, OutputInterface $output)
    {
        $tableName = $this->selectTable($input);

//        $where = $this->params['search'];
//        $where = collection($where)->map(function ($item) {
//            return "'{$item}' => \$params['{$item}']";
//        })->toArray();
//
        $listWhere = $infoWhere = [];
        $listSearch = $input->getArgument('list:search');
        if (!empty($listSearch)) {
            $listWhere = collect($listSearch)->map(function ($item) {
                return "'{$item}' => \$params['{$item}']";
            })->toArray();
        }
        $infoSearch = $input->getArgument('info:search');
        if (!empty($infoSearch)) {
            $infoWhere = collect($infoSearch)->map(function ($item) {
                return "'{$item}' => \$params['{$item}']";
            })->toArray();
        }

        $paramsFilter = '        !isset($params[\'id\']) && $this->paramsError(\'id\');';
        if (!empty($this->params['search'])) {
            $paramsFilter = collect($this->params['search'])->map(function ($item) {
                return "        !isset(\$params['{$item}']) && \$this->paramsError('{$item}');";
            })->toArray();
        }
        $this->info("[*] start to get table info!");
        $serviceName = CodeService::getInstance()->getClassName($tableName, 'Service');
        $comment = $this->tableService->getTableComment($tableName);

        $modelName = CodeService::getInstance()->getClassName($tableName, 'Model');

        $indexList = $this->tableService->getTableIndex($tableName);
        $indexList = $this->tableService->getUniqueFields($indexList);
        $uniqueCheck = '';
        foreach ($indexList as $item) {
            $params = [];
            foreach ($item as $index) {
                $index != 'deleted_at' && $params[] = "'{$index}' => \$params['{$index}']";
            }
            $uniqueCheck .= $this->getUniqueFunction($modelName, join(",", $params));
        }

        //获取编辑唯一监测
        $params = [
            "indexList" => var_export($indexList, true),
            "model" => $modelName,
        ];
        $editUniqueCheck = $this->getEditUniqueCode($params);
        $params = [
            'controllerName' => $serviceName,
            'date' => date("Y-m-d H:i:s"),
            'desc' => $comment,
            'ruleName' => CodeService::getInstance()->getClassName($tableName, 'Rule'),
            'transName' => CodeService::getInstance()->getClassName($tableName, 'Trans'),
            'table' => CodeService::getInstance()->getClassName($tableName),
            'configName' => CodeService::getInstance()->getClassName($tableName, 'Config'),
            'modelName' => $modelName,
            'serviceName' => CodeService::getInstance()->getClassName($tableName, 'Service'),
            'paramsFilter' => @join("\n", $paramsFilter),
            'listWhere' => join(",", $listWhere),
            'infoWhere' => join(",", $infoWhere),
            'uniqueCheck' => $uniqueCheck,
            'editUniqueCheck' => $editUniqueCheck,
        ];

        $this->info("[*] start to get service code!");
        $code = TemplateService::getInstance()->getCode($this->template, $params);
        $file = ConfigService::getModuleName() . 'services/' . $serviceName . ".php";
        $this->info("[*] start to save service file!");
        FileService::getInstance()->saveCode($file, $code);
        $this->info("[*] save sevice[{$serviceName}] success!");
    }

    public function getUniqueFunction($model, $params)
    {
        return "//重复数据监测
        \$this->exists(\$this->model->where([{$params}]));";
    }

    public function getEditUniqueCode($params)
    {
        return CodeService::getInstance()->getCode('EditUniqueCheck.blade.php', $params);
    }
}
