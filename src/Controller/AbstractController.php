<?php
/**
 * Created by PhpStorm.
 * User: Call
 * Date: 17/08/2016
 * Time: 00:14
 */

namespace Home\Controller;


use Admin\Model\Inscrition\InscritionRepository;
use Auth\Storage\IdentityManager;
use Base\Model\Cache;
use Interop\Container\ContainerInterface;
use Suricatis\Model\Inscritos\InscritosRepository;
use Zend\Debug\Debug;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

abstract class AbstractController extends AbstractActionController {

    /**
     * @var $containerInterface ContainerInterface
     */
    protected $containerInterface;
    protected $IdentityManager;
    protected $table;
    protected $model;
    protected $form;
    protected $filter;
    protected $route;
    protected $controller;
    protected $action;
    protected $id;
    protected $page;
    protected $data;
    protected $user;
    protected $config;
    /**
     * @var $cache Cache
     */
    protected $cache;

    /**
     * @param MvcEvent $e
     * @return mixed
     */
    public function onDispatch(MvcEvent $e)
    {
        $this->cache=$this->containerInterface->get(Cache::class);
        $this->config=$this->containerInterface->get('ZfConfig');
        return parent::onDispatch($e);
    }

    /**
     * @param ContainerInterface $containerInterface
     */
    abstract  function __construct(ContainerInterface $containerInterface);

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->containerInterface->get($this->table);
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->containerInterface->get($this->model);
    }

    /**
     * @return mixed
     */
    public function getForm()
    {
        if(!empty($this->form))
        return $this->containerInterface->get($this->form);
    }

    /**
     * @return mixed
     */
    public function getFilter()
    {
        return $this->containerInterface->get($this->filter);
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        if($this->params()->fromPost()){
            $this->data=array_merge_recursive($this->params()->fromPost(),$this->params()->fromFiles());
        }
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getIdentityManager()
    {
        $this->IdentityManager=$this->containerInterface->get(IdentityManager::class);
        $this->user=$this->IdentityManager->hasIdentity();
        return $this->IdentityManager;
    }

    public function accessdenyAction()
    {
        $view=new ViewModel();
        return $view;
    }

    public function indexAction(){

        return new ViewModel(['user'=>$this->user]);
    }

    public function politicadeprivacidadeAction(){
        $issusers=$this->cache->getItem('issusers');
        return new ViewModel(['user'=>$this->user,'issusers'=>$issusers]);
    }

    public function termosdeusoAction(){

        $issusers=$this->cache->getItem('issusers');
        return new ViewModel(['user'=>$this->user,'issusers'=>$issusers]);
    }

    public function notificationAction(){

        return new JsonModel([]);
    }
    public function autocompleteAction(){
        $grupos=[];
        $this->table=InscritosRepository::class;
        $this->getTable()->findBy();
        if($this->getTable()->getData()->getResult()){
            foreach($this->getTable()->getData()->getData() as $g){
                $grupos[$g->getGrupoId()]=$g->getGrupoId();
            }
        }
       return new JsonModel($grupos);
    }





} 