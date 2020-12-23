<?php
/**
 * Created by PhpStorm.
 * @file   ModelCommand.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2020/11/10 3:25 下午
 * @desc   ModelCommand.php
 */

namespace Generate\Command;

use Generate\Services\CodeService;
use Generate\Services\ConfigService;
use Generate\Services\FileService;
use Generate\Services\TemplateService;
use Generate\Common\GenerateCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModelCommand extends GenerateCommand
{

    protected $name = "generate:model";

    protected $description = "自动生成Model脚本";

    protected $template = 'ModelTemplate.tmp';

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function handle(InputInterface $input, OutputInterface $output)
    {
        $tableName = $this->selectTable($input);
        $this->info("[*] start to get table info!");
        $modelName = CodeService::getInstance()->getClassName($tableName, 'Model');
        $comment = $this->tableService->getTableComment($tableName);
        $tableFields = $this->tableService->getFields($tableName);
        $fields = collect($tableFields)->map(function ($item) {
            $item = (array)$item;
            if ($item['data_type'] == 'int') {
                return " * @property integer \${$item['column_name']} {$item['column_comment']}";
            } else {
                return " * @property string \${$item['column_name']} {$item['column_comment']}";
            }
        })->toArray();
        $table = CodeService::getInstance()->getClassName($tableName);

        $indexList = $this->tableService->getTableIndex($tableName);
        $indexList = $this->tableService->getUniqueFields($indexList);

        $params = [
            'model' => $modelName,
            'date' => date("Y-m-d H:i:s"),
            'desc' => $comment,
            'fields' => join("\n", $fields),
            'true_table' => $tableName,
            'table' => $table,
            'cachePrefix' => strtoupper($table),
            'configName' => CodeService::getInstance()->getClassName($tableName, 'Config'),
            'index' => var_export($indexList, true),
        ];

        $this->info("[*] start to get model code!");
        $code = TemplateService::getInstance()->getCode($this->template, $params);
        $file = "models/" . $modelName . ".php";
        $this->info("[*] start to save model file!");
        FileService::getInstance()->saveCode($file, $code);
        $this->info("[*] save model[{$modelName}] success!");
    }
}
