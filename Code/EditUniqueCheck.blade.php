
$indexList = #indexList#;
foreach ($indexList as $indexFields) {
    $where = [];
    $isUse = false;
    foreach ($indexFields as $field) {
        if (!isset($params[$field])) {
            $where[$field] = $model->$field;
        } else {
            $model->$field != $params[$field] && $isUse = true;
            $where[$field] = $params[$field];
        }
    }
    //重复数据监测
    if ($isUse) {
        $result = #model#::where($where)->exists();
        $result && $this->apiThrowError(ErrorConfig::ER_DATA_REPEAT);
    }
}
