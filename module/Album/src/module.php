<?php
/**
 * Created by PhpStorm.
 * User: liyu
 * Date: 2018/8/21
 * Time: 下午2:02
 */

namespace Album;

use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}