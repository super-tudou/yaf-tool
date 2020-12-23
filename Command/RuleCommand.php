<?php
/**
 * Created by PhpStorm.
 * @file   RuleCommand.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2020/11/10 5:14 下午
 * @desc   RuleCommand.php
 */

namespace Generate\Command;


use Generate\Common\GenerateCommand;
use Generate\Services\CodeService;
use Generate\Services\ConfigService;
use Generate\Services\FileService;
use Generate\Services\TemplateService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RuleCommand extends GenerateCommand
{

    protected $name = "generate:rule";

    protected $description = "自动生成规则脚本";

    protected $template = 'RuleTemplate.tmp';

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function handle(InputInterface $input, OutputInterface $output)
    {
        $tableName = $this->selectTable($input);
        $this->info("[*] start to create response design .....");
        $this->info("[*] start to get table info!");
        $responseName = CodeService::getInstance()->getClassName($tableName, 'Rule');
        $configName = CodeService::getInstance()->getClassName($tableName, 'Config');
        $comment = $this->tableService->getTableComment($tableName);
        $tableFields = $this->tableService->getFields($tableName);
        $fieldRule = $listRuleSource = $this->tableService->getTableFieldDesign($tableFields);
        $fieldRule = collect($fieldRule)->map(function ($item) {
            $item = (array)$item;
            if ($item['type'] == 'integer') {
                return "        '{$item['field']}' => '{$item['type']}|min:1'";
            } elseif (in_array($item['field'], ['deleted_at', 'updated_at', 'created_at'])) {
                return "        '{$item['field']}' => '{$item['type']}'";
            }
            return "        '{$item['field']}' => '{$item['type']}|min:1|max:{$item['length']}'";
        })->toArray();

        $listSearch = $input->getArgument('list:search');
        if (!empty($listSearch)) {
            $listRule = collect($listSearch)->map(function ($item) {
                return "            '{$item}' => 'required|' . self::FIELD_RULE['{$item}']";
            });
        } else {
            $listRule = collect($listRuleSource)->map(function ($item) {
                return '';
//                return "            '{$item['field']}' => 'required|' . self::FIELD_RULE['{$item['field']}']";
            });
        }
        $listRule = $listRule->merge(["            'pos' => 'required|integer|min:0'"])
            ->merge(["            'limit' => 'required|integer|min:1|' . self::FIELD_RULE['limit'],"])->toArray();

        $createRule = collect($listRuleSource)->map(function ($item) {
            $item = (array)$item;
            if (!in_array($item['field'], ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                return "            '{$item['field']}' => 'required|' . self::FIELD_RULE['{$item['field']}']";
            }
        })->toArray();
        $edieRule = collect($listRuleSource)->map(function ($item) {
            $item = (array)$item;
            if (!in_array($item['field'], ['created_at', 'updated_at', 'deleted_at'])) {
                if (!in_array($item['field'], ['id'])) {
                    return "            '{$item['field']}' => self::FIELD_RULE['{$item['field']}']";
                }
                return "            '{$item['field']}' => 'required|' . self::FIELD_RULE['{$item['field']}']";
            }
        })->toArray();

        $deleteRule = ["            'id' => 'required|' . self::FIELD_RULE['id']"];
        $infoRule = ["            'id' => 'required|integer|min:1'"];

        $infoSearch = $input->getArgument('info:search');
        if (!empty($infoSearch)) {
            $infoRule = collect($infoSearch)->map(function ($item) {
                return "            '{$item}' => 'required|' . self::FIELD_RULE['{$item}']";
            })->toArray();
        }

        $params = [
            'config' => $configName,
            'ruleName' => $responseName,
            'date' => date("Y-m-d H:i:s"),
            'desc' => $comment,
            'field_rule' => join(",\n", $fieldRule),
            'list_rule' => join(",\n", array_filter($listRule)),
            'create_rule' => join(",\n", array_filter($createRule)),
            'edit_rule' => join(",\n", array_filter($edieRule)),
            'delete_rule' => join(",\n", array_filter($deleteRule)),
            'info_rule' => join(",\n", array_filter($infoRule)),
        ];
        $this->info("[*] start to get response code!");
        $code = TemplateService::getInstance()->getCode($this->template, $params);
        $file = ConfigService::getModuleName() . 'rules/' . $responseName . ".php";
        $this->info("[*] start to save response file!");
        FileService::getInstance()->saveCode($file, $code);
        $this->info("[*] save response[{$responseName}] success!");
    }
}
