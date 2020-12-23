<?php
/**
 * Created by PhpStorm.
 * @file   ConfigCommand.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2020/11/10 1:38 下午
 * @desc   ConfigCommand.php
 */

namespace Generate\Command;


use Generate\Services\CodeService;
use Generate\Services\ConfigService;
use Generate\Services\FileService;
use Generate\Services\TemplateService;
use Generate\Common\GenerateCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigCommand extends GenerateCommand
{


    protected $name = "generate:config";

    protected $description = "自动生成配置脚本";

    protected $template = 'ConfigTemplate.tmp';

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    public function handle(InputInterface $input, OutputInterface $output)
    {
        $tableName = $this->selectTable($input);
        $this->info("[*] start to create config .....");
        $this->info("[*] start to get table info!");

        $configName = CodeService::getInstance()->getClassName($tableName, 'Config');

        $table = CodeService::getInstance()->getClassName($tableName);
        $comment = $this->tableService->getTableComment($tableName);
        $tableFields = $this->tableService->getFields($tableName);

        $fields = collect($tableFields)->map(function ($item) {
            $item = (array)$item;
            return "     * {$item['column_name']}\t{$item['column_type']}\t{$item['column_comment']}";
        })->toArray();
        $params = [
            'config' => $configName,
            'date' => date("Y-m-d H:i:s"),
            'desc' => $comment,
            'fields' => join("\n", $fields),
            'cachePrefix' => strtoupper($table),
            'table' => strtolower($table),
        ];
        $this->info("[*] start to get config code!");
        $code = TemplateService::getInstance()->getCode($this->template, $params);
        $file = ConfigService::getModuleName() . 'configs/' . $configName . ".php";
        $this->info("[*] start to save config file!");
        FileService::getInstance()->saveCode($file, $code);
        $this->info("[*] save config[** {$configName} **] success!");
    }
}
