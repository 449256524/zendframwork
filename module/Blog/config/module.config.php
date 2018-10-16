<?php
/**
 * Created by PhpStorm.
 * User: liyu
 * Date: 2018/10/16
 * Time: 下午3:46
 */

namespace Blog;

return array(
    // 该行为 RouteManager 打开配置
    'router' => array(
        // 打开所有可能路径的配置O
        'routes' => array(
            // 定义一个新路径，称为 "post"
            'post' => array(
                // 定义一个路径 "Zend\Mvc\Router\Http\Literal" , 基本上就是一个字符串
                'type' => 'literal',
                // 配置路径本身
                'options' => array(
                    // 监听 uri "/blog"
                    'route'    => '/blog',
                    // 定义默认控制器和当这个路径匹配时需要执行的动作
                    'defaults' => array(
                        'controller' => 'Blog\Controller\List',
                        'action'     => 'index',
                    )
                )
            )
        )
    )
);