<?php
/**
 * Created by PhpStorm.
 * @file   GenerateCommand.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2020/11/11 11:02 上午
 * @desc   GenerateCommand.php
 */

namespace Generate\Command;


use Generate\Services\TableService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends \Generate\Common\GenerateCommand
{
    protected $name = "generate:generate";
    protected $description = "自动生成接口脚本";

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function handle(InputInterface $input, OutputInterface $output)
    {
        date_default_timezone_set("PRC");
        $tableName = $this->selectTable($input);
        $tableFields = collect($this->tableService->getFields($tableName))->map(function ($item) {
            $item = (array)$item;
            return $item['column_name'];
        })->merge('null')->toArray();

        $listSearchFields = $this->choice("[2]请列表选择搜索的字段:(多个用逗号分隔)", $tableFields);
        if (empty($listSearchFields)) {
            $this->error("未选择字段，请重新选择!");
        }
        $infoSearchFields = $this->choice("[2]请详情选择搜索的字段:(多个用逗号分隔)", $tableFields);
        if (empty($infoSearchFields)) {
            $this->error("未选择字段，请重新选择!");
        }

        $params = [
            'table' => $tableName,
            'list:search' => $listSearchFields[0] == 'null' ? [] : $listSearchFields,
            'info:search' => $infoSearchFields[0] == 'null' ? [] : $infoSearchFields,
        ];

        $this->line("开始创建[$tableName]模型");
        //生成模型
        $this->executeCommand("generate:model", $params, $output);
        $this->line("创建[$tableName]模型完成");
        //创建转换
        $this->line("开始创建[$tableName]转换层");
        $this->executeCommand("generate:trans", $params, $output);
        $this->line("创建[$tableName]转换层完成");
        //创建配置文件
        $this->line("开始创建[$tableName]配置文件");
        $this->executeCommand("generate:config", $params, $output);
        $this->line("创建[$tableName]配置文件完成");
        //创建规则
        $this->line("开始创建[$tableName]规则");
        $this->executeCommand("generate:rule", $params, $output);
        $this->line("创建[$tableName]规则完成");
        //生成服务
        $this->line("开始创建[$tableName]服务");
        $this->executeCommand("generate:service", $params, $output);
        $this->line("创建[$tableName]服务完成");
        //生成控制器
        $this->line("开始创建[$tableName]控制器");
        $this->executeCommand("generate:controller", $params, $output);
        $this->line("创建[$tableName]控制器完成");
        //生成输出转换
        $this->line("开始创建[$tableName]输出转换");
        $this->executeCommand("generate:response", $params, $output);
        $this->line("创建[$tableName]输出转换完成");
    }
}
