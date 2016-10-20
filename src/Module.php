<?php
/**
 * Created by PhpStorm.
 * User: Call
 * Date: 16/08/2016
 * Time: 23:52
 */

namespace Home;

use Home\View\Helper\HomeHelper;
use Home\View\Helper\ReadHelper;
use Interop\Container\ContainerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;

class Module implements ConfigProviderInterface, ViewHelperProviderInterface{

    /**
     * Returns configuration to merge with application configuration
     *
     * @return array|\Traversable
     */
    public function getConfig()
    {
        return include __DIR__.'/../config/module.config.php';
    }
    /**
     * Expected to return \Zend\ServiceManager\Config object or array to
     * seed such an object.
     *
     * @return array|\Zend\ServiceManager\Config
     */
    public function getViewHelperConfig()
    {
        return [
            'invokables' => [
                'OpenGraph' => 'Home\View\Helper\OpenGraph',
                //Override
                'headmeta' => 'Home\View\Helper\HeadMeta',

            ],
            'factories'=>[
               'HomeHelper'=>function(ContainerInterface $containerInterface){
                   return new HomeHelper($containerInterface);
               },
                'ReadHelper'=>function(ContainerInterface $containerInterface){
                    return new ReadHelper($containerInterface);
                }
            ]
        ];
    }
}