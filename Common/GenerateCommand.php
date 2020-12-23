<?php
/**
 * Created by PhpStorm.
 * @file   GenerateCommand.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2020/11/9 7:49 下午
 * @desc   GenerateCommand.php
 */

namespace Generate\Common;


use Generate\Services\TableService;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class GenerateCommand extends AbstractCommand
{
    /**
     * 参数
     * @var array
     */
    protected $params = [];

    /**
     * @var TableService
     */
    protected $tableService;
    /**
     * @var Collection
     */
    protected $tableList;
    protected $template = 'ConfigTemplate.tmp';

    /**
     * GenerateCommand constructor.
     * @param array $params
     */
    protected function __construct($params = [])
    {
        $this->params = $params;
        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function __init(InputInterface $input, OutputInterface $output)
    {
        $this->tableService = TableService::getInstance();
        $this->tableList = collect($this->params['table_list'] ?? $this->tableService->getTableList());
    }

    protected function configure()
    {
        $this->addArgument('table', InputArgument::OPTIONAL, 'select table name');
        $this->addArgument('list:search', InputArgument::OPTIONAL, 'list search field');
        $this->addArgument('info:search', InputArgument::OPTIONAL, 'info search field');
    }

    protected function selectTable(InputInterface $input)
    {
        $table = $input->getArgument('table');
        if (empty($table)) {
            $tableName = $this->askWithCompletion("请选择要操作的表:", $this->tableList->toArray());
            if (!$this->tableList->contains($tableName)) {
                $this->error("选择的表不存在，请重新选择!");
            }
        } else {
            $tableName = $table;
        }
        return $tableName;
    }

    protected function executeCommand($command, $arguments, OutputInterface $output)
    {
        $command = $this->getApplication()->find($command);
        $greetInput = new ArrayInput($arguments);
        return $command->run($greetInput, $output);

    }
}
