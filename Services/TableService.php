<?php

namespace Generate\Services;

use Illuminate\Support\Facades\DB;

/**
 * Class TableService
 * @package App\Services\Database
 *
 */
class TableService extends AbstractService
{

    /**
     * @return mixed
     */
    protected function __init()
    {
        // TODO: Implement __init() method.
    }

    /**
     * 获取表列表
     * @param string $table
     * @return array
     */
    public function getTableList(string $table = '')
    {
        $sql = 'show tables';
        empty($table) || $sql .= " where Tables_in_laputa like '%{$table}%'";
        $result = DB::connection()->select($sql);
        return collect($result)->map(function ($item) {
            return end($item);
        })->toArray();

    }

    /**
     * @param string $tableName
     * @return array
     */
    public function getFields(string $tableName)
    {
        $sql = "select column_name,column_comment,data_type,column_type from information_schema.columns  where table_name='{$tableName}'
and TABLE_SCHEMA='" . DATABASE . "'";
        return DB::connection()->select($sql);
    }

    /**
     * @param string $table
     * @return array
     */
    public function getTableComment(string $table)
    {
        $sql = "SELECT TABLE_NAME,TABLE_COMMENT as `comment` FROM information_schema.TABLES WHERE table_name='{$table}';";
        $result = DB::connection()->select($sql);
        return $result[0]->comment;
    }

    public function getTableFieldDesign($fields)
    {
        return collect($fields)->map(function ($item) {
            $item = (array)$item;
            return $this->getFieldTypeDesign($item);
        })->toArray();
    }

    public function getTableIndex(string $tableName)
    {
        $sql = "show index from {$tableName} where non_unique=0 and key_name !='PRIMARY';";
        return DB::connection()->select($sql);
    }

    public function getUniqueFields($index)
    {
        return collect($index)->map(function ($item) {
            return (array)$item;
        })->groupBy("Key_name")->filter(function ($item, $index) {
            return $index != 'PRIMARY';
        })->map(function ($item) {
            return collect($item)->map(function ($item) {
                return $item['Column_name'];
            });
        })->toArray();
    }


    public function getFieldTypeDesign($field)
    {
        if (strpos($field['column_type'], 'int') !== false) { //int类型
            $length = intval(str_replace(["int", '(', ')', 'unsigned'], '', $field['column_type']));
            return [
                'field' => $field['column_name'],
                'type' => 'integer',
                'length' => $length,
                'comment' => $field['column_comment']
            ];
        } elseif (strpos($field['column_type'], 'varchar') == false) { //int类型
            $length = intval(str_replace(["varchar", '(', ')'], '', $field['column_type']));
            return [
                'field' => $field['column_name'],
                'type' => 'string',
                'length' => $length,
                'comment' => $field['column_comment']
            ];
        } else {
            var_dump($field['column_type']);
        }
        return [
            'field' => $field['column_name'],
            'type' => 'string',
            'length' => 10,
            'comment' => $field['column_comment']
        ];
    }

}
