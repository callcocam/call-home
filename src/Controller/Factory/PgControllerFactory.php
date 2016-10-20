<?php
/**
 * Created by PhpStorm.
 * User: Claudio
 * Date: 10/10/2016
 * Time: 11:14
 */

namespace Home\Controller\Factory;


use Home\Controller\PgController;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class PgControllerFactory implements FactoryInterface {

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
       return new PgController($container);
    }
}