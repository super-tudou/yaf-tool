<?php
/**
 * Created by PhpStorm.
 * @file   CommandService.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2020/11/10 11:28 上午
 * @desc   CommandService.php
 */

namespace Generate\Services;

use Generate\Common\AbstractCommand;

class CommandService extends AbstractService
{

    /**
     * @return mixed
     */
    protected function __init()
    {
        // TODO: Implement __init() method.
    }

    public static function getAllCommand()
    {
        $commandPath = dirname(__DIR__);
        $commands = glob("$commandPath/Command/*");
        $commandList = [];
        foreach ($commands as $command) {
            $command = 'Generate\Command\\' . str_replace(".php", '', basename($command));
            $command = $command::getInstance();
            if ($command instanceof AbstractCommand) {
                $commandList[] = $command::getInstance();
            }
        }
        return $commandList;
    }


    /**
     * get command list
     * @return array
     */
    public static function getCommandList()
    {
        $commands = self::getAllCommand();
        $commandList = [];
        foreach ($commands as $command) {
            $commandList[$command->getName()] = function () use ($command) {
                return $command;
            };
        }
        return $commandList;
    }

}
