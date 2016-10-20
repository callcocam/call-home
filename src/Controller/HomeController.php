<?php
/**
 * Created by PhpStorm.
 * User: Call
 * Date: 16/08/2016
 * Time: 23:50
 */

namespace Home\Controller;


use Admin\Model\Inscrition\Inscrition;
use Admin\Model\Inscrition\InscritionRepository;
use Base\Services\Client;
use Interop\Container\ContainerInterface;
use Mail\Service\Mail;
use Zend\View\Model\JsonModel;
use Zend\Xml2Json\Xml2Json;
use Zend\View\Model\ViewModel;

class HomeController extends AbstractController {

    /**
     * @param ContainerInterface $containerInterface
     */
    function __construct(ContainerInterface $containerInterface)
    {
        $this->containerInterface=$containerInterface;
    }

    public function assinaturasAction(){
        $this->table=PgassinaturasRepository::class;
        $this->getTable()->select();
        return new JsonModel($this->getTable()->getCurrents());

    }

    public function notificationAction(){
        if($this->getData())
        {
            $config=$this->containerInterface->get('Config');
            $result=[];
            $this->data["PAGSEGURO_ENV"] =$config['pag-seguro']["PAGSEGURO_ENV"];// "sandbox";
            $this->data["PAGSEGURO_EMAIL"] =$config['pag-seguro']["PAGSEGURO_EMAIL"];// "callcocam@gmail.com";
            $this->data["PAGSEGURO_TOKEN_SANDBOX"] =$config['pag-seguro']["PAGSEGURO_TOKEN_SANDBOX"];// "BEFA9E61638143A39CEAA12C0175D324";
             /**
             * @var $client Client
             */
            $client=$this->containerInterface->get(Client::class);
            $client->setUri(sprintf("%s/%s",$this->config->serverHost,'pg/notification'));
            $client->setMethod('POST');
            $client->setParameterPost($this->data);
            $response = $client->send();
            if ($response->isSuccess()) {
                $this->table=InscritionRepository::class;
                $this->model=Inscrition::class;
                $result=json_decode($response->getBody(),true);
                $this->getTable()->findOneBy(['codigo'=>$result['reference']],false);
                if($this->getTable()->getData()->getResult()){
                    $model=$this->getModel();
                    $model->exchangeArray($this->getTable()->getCurrents());
                    $model->setSenderHash($this->data["notificationCode"]);
                    if($result['status']==='3'){
                        $model->setState(0);
                        //PEGAR OS DADOS DO USUARIO RECEM CADASTARDO
                        //ADCIONAMOS A URL COM A KEY A VAR DATA
                         //PEGAMOS O SERVIÇO DE EMAIL
                        $mail = $this->containerInterface->get(Mail::class);
                        //SETAMOS AS INFORMAÇÕES DE ENVIO
                        //:assunto ->Subject
                        //:email do usuario que se cadastro ->To
                        //:dados do email ->Data
                        //:template de email ->Template
                        $mail->setSubject('SEU CODIGO DE INCRIÇÃO')
                            ->setTo($model->getEmail())
                            ->setData($model->toArray())
                            ->setViewTemplate('inscricao-codigo');
                        $mail->send();
                    }
                    $this->getTable()->update($model);
                }
            }

        }
        die;
    }
    //98437067
}