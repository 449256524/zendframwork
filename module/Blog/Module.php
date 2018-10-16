<?php
/**
 * Created by PhpStorm.
 * User: liyu
 * Date: 2018/10/16
 * Time: 下午3:31
 */

namespace Blog;


use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements  ConfigProviderInterface
{
    public function getConfig()
    {
        // TODO: Implement getConfig() method.
        return include __DIR__ . '/config/module.config.php';
    }
}