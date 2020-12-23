<?php
/**
 * Created by PhpStorm.
 * @file   Application.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2020/11/10 10:35 上午
 * @desc   Application.php
 */

namespace Generate\App;


use Generate\Common\AbstractBase;
use Generate\Library\DbQuery;
use Generate\Services\ConfigService;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

class Application extends AbstractBase
{
    protected $config;

    /**
     * @return mixed
     */
    protected function __init()
    {
        $this->config = ConfigService::getInstance()->getConfig();
        $dbQuery = DbQuery::getInstance();
        $dbQuery->setParams($this->config['database']['default'])->openDb();
        define('DATABASE', $this->config['database']['default']['database']);
    }

    public function run()
    {
        $capsule = new DB;
        $config = $this->config['database']['default'];
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => $config['host'],
            'database' => $config['database'],
            'username' => $config['username'],
            'password' => $config['password'],
            'charset' => $config['charset'],
            'collation' => $config['collation'],
            'prefix' => '',
        ]);
        $capsule->setEventDispatcher(new Dispatcher(new Container));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        \Illuminate\Support\Facades\DB::swap($capsule);
//        $data = DB::table('users')->limit(10)->select('*')->get();
//        $data = DB::select("show tables");
//        print_r($data);
//        exit;
    }
}
