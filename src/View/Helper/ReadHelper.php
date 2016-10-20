<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 01/09/2016
 * Time: 08:27
 */

namespace Home\View\Helper;


use Base\Model\Check;
use Base\Model\Result;
use Interop\Container\ContainerInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\View\Helper\AbstractHelper;

class ReadHelper extends AbstractHelper {


    protected static $html = array();
    protected static $labels = array();
    protected static $hidden = array();

    const MSG_SUCCESS="SUA BUSCA RETORNOU %s";
    const MSG_ERROR="SUA BUSCA RETORNOU NENHUM RESULTADO";
    protected $MES=['Jan'=>'Janeiro','Feb'=>'Fevereiro','Mar'=>'Março','Apr'=>'Abril','May'=>'Maio','Jun'=>"Junho",'Jul'=>'Julho','Aug'=>'Agosto','Sep'=>'Setembro','Oct'=>'Outubro','Nov'=>'Novembro','Dec'=>'Dezembro'];
    protected $DIASEMANA=['1'=>"Seg",'2'=>"Terç",'3'=>"Quart",'4'=>"Quint",'5'=>"Sext",'6'=>"Sab",'7'=>"Dom"];
    protected $categorias=['onde-comprar'=>"ONDE COMPRAR",'onde-comer'=>"ONDE COMER",'onde-ficar'=>"ONDE FICAR",'onde-se-divertir'=>"ONDE SE DIVERTIR",
        'quero-comprar'=>"QUERO COMPRAR",'quero-vender'=>"QUERO VENDER",'quero-trocar'=>"QUERO TROCAR",'quero-alugar'=>"QUERO ALUGAR"];
    protected $config;

    public $tirar=true;
    /**
     * @var $data Result
     */
    protected $data;

    private $linha;

    protected $select=' * ';
    /**
     * @var Keys
     */
    private $Keys;
    /**
     * @var Values
     */
    private $Values;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container){

        $this->container = $container;
        $this->config=$this->container->get("ZfConfig");


    }

    /**
     * @return array
     */
    public function getDIASEMANA($key)
    {
        if(isset($this->DIASEMANA[$key])){
            return $this->DIASEMANA[$key];
        }
        return $key;
    }


    public function ExeRead($tabela,$extraselect="",$params=[],$current=false)
    {
        // echo sprintf('SELECT %s FROM %s %s  ',$this->select,$tabela,$extraselect);die;

        $this->setData(new Result());
        $this->data->setError(self::MSG_ERROR);
        $this->data->setResult(false);
        $adapter =$this->container->get(AdapterInterface::class);
        $statement = $adapter->createStatement(sprintf('SELECT %s FROM %s %s  ',$this->select,$tabela,$extraselect));
        $results = $statement->execute($params);
        if($results->count()){
            $this->data->setResult($results->count());
            if($current){
                $this->data->setData($results->current());
            }
            else{
                $this->data->setData($this->paginator($results));
            }

            $this->data->setError(sprintf(self::MSG_SUCCESS,$results->count()));
        }
        return $this->getData();
    }



    public function paginator($data){
        $resultSet = new ResultSet(); //$this->tableGateway->getResultSetPrototype();
        $resultSet->initialize($data);
        $paginator=new Paginator(new ArrayAdapter($resultSet->toArray()));
        $paginator->setCurrentPageNumber($this->view->page);
        $paginator->setItemCountPerPage($this->config->count_per_page);
        return $paginator;
    }



    /**
     * @param string $select
     */
    public function setSelect($select)
    {
        $this->select = $select;
    }



    public function alert($msg,$tipo="danger"){
        $alert=$this->view->Html('div')->setClass("alert alert-{$tipo}");
        $button=$this->view->Html('button')->setAttributes(['type'=>"button",'data-dismiss'=>"alert",'aria-hidden'=>"true"])->setText(PHP_EOL)->appendText('&times;')->appendText(PHP_EOL);
        $strong=$this->view->Html('strong')->setText(PHP_EOL)->appendText($msg);
        $alert->setText(PHP_EOL)->appendText($button)->appendText(PHP_EOL)->appendText($strong)->appendText(PHP_EOL);
        echo $alert;

    }

    /**
     * <b>Exibir Template View:</b> Execute um foreach com um getResult() do seu model e informe o envelope
     * neste método para configurar a view. Não esqueça de carregar a view acima do foreach com o método Load.
     * @param array $Linha = Array com dados obtidos
     * @param View $View = Template carregado pelo método Load()
     * @param string $action
     * @param int $wordTitle
     * @param int $wordDesc
     * @return mixed
     */
    public function Show(array $Linha, $View,$action="index",$wordTitle=12,$wordDesc=38,$param_url="url") {

        if(isset($Linha[$param_url])){
            $Linha[$param_url]=$this->view->url('home/default',['controller'=>'home','action'=>$action,'id'=>$Linha[$param_url]]);
        }
        if(isset($Linha['title'])){
            $Linha['title']= Check::Words($Linha['title'], $wordTitle);
        }
        if(isset($Linha['description'])){
            $Linha['description'] = Check::Words($Linha['description'], $wordDesc,null,$this->tirar);
        }
        if(isset($Linha['created'])){
            $Linha['datetime'] = date('Y-m-d', strtotime($Linha['created']));
            $Linha['dia'] = date('d', strtotime($Linha['created']));
            $Linha['mes'] = $this->MES[date('M', strtotime($Linha['created']))];
            $Linha['ano'] = date('Y', strtotime($Linha['created']));
        }
        if(isset($Linha['publish_up'])){
            $Linha['datetime'] = date('Y-m-d', strtotime($Linha['publish_up']));
            $Linha['dia_pub_up'] = date('d', strtotime($Linha['publish_up']));
            $Linha['mes_pub_up'] = $this->MES[date('M', strtotime($Linha['publish_up']))];
            $Linha['ano_pub_up'] = date('Y', strtotime($Linha['publish_up']));
            $Linha['hrs_pub_up'] = date('H', strtotime($Linha['publish_up']));
            $Linha['min_pub_up'] = date('i', strtotime($Linha['publish_up']));
            $Linha['seg_pub_up'] = date('s', strtotime($Linha['publish_up']));

        }

        if(isset($Linha['publish_down'])){
            $Linha['datetime'] = date('Y-m-d', strtotime($Linha['publish_down']));
            $Linha['dia_pub_down'] = date('d', strtotime($Linha['publish_down']));
            $Linha['mes_pub_down'] = $this->MES[date('M', strtotime($Linha['publish_down']))];
            $Linha['ano_pub_down'] = date('Y', strtotime($Linha['publish_down']));
            $Linha['hrs_pub_down'] = date('H', strtotime($Linha['publish_down']));
            $Linha['min_pub_down'] = date('i', strtotime($Linha['publish_down']));
            $Linha['seg_pub_down'] = date('s', strtotime($Linha['publish_down']));

        }
        if(isset($Linha['modified'])){
            $Linha['pubdate'] = date('d/m/Y H:i', strtotime($Linha['modified']));
            $Linha['horas'] = date('H:i', strtotime($Linha['modified']));
        }

        if(isset($Linha['images'])){
            $Linha['images']=str_replace('\\','/',$Linha['images']);
        }
        $this->setKeys($Linha);
        $this->setValues();
        return $this->ShowView($View);
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getCategorias($key="NÃO ENCONTRADA")
    {

        if(isset($this->categorias[$key])){
            return $this->categorias[$key];
        }
        return $key;
    }

    /*
    * ***************************************
    * **********  PRIVATE METHODS  **********
    * ***************************************
    */

    //Executa o tratamento dos campos para substituição de chaves na view.
    private function setKeys($Linha) {
        $this->linha = $Linha;
        $this->Keys = explode('&', '#' . implode("#&#", array_keys($this->linha)) . '#');
    }

    //Obtém os valores a serem inseridos nas chaves da view.
    private function setValues() {
        $this->Values = array_values($this->linha);
    }

    //Exibe o template view com echo!
    private function ShowView($View) {
        return str_replace($this->Keys, $this->Values, $View);
    }

} 