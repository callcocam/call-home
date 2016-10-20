<?php
/**
 * Created by PhpStorm.
 * User: Claudio
 * Date: 10/10/2016
 * Time: 09:49
 */

namespace Home\Controller;


use Admin\Model\Pagseguro\Pagseguro;
use Admin\Model\Pagseguro\PagseguroRepository;
use Base\Services\Client;
use Interop\Container\ContainerInterface;
use Mail\Service\Mail;
use Suricatis\Form\InscritosForm;
use Suricatis\Model\Evento\EventoRepository;
use Suricatis\Model\Inscritos\Inscritos;
use Suricatis\Model\Inscritos\InscritosRepository;
use Zend\Debug\Debug;
use Zend\View\Model\JsonModel;

class PgController extends AbstractController {

    /**
     * @param ContainerInterface $containerInterface
     */
    function __construct(ContainerInterface $containerInterface)
    {
        $this->containerInterface=$containerInterface;
        $this->table=PagseguroRepository::class;
        $this->model=Pagseguro::class;
    }
    public function inscrevaseAction()
    {
        $submitMessage[]='Sua inscrição foi recebida com sucesso.';
        $submitMessage['email']='Nós enviamos para o email:"" as instruções de pagamento da isncrição';
        $successParagraph=[];
        $error=null;
        $success=null;
        $response=null;
        if(!$this->cache->hasItem('issusers')){
            $this->containerInterface->get('issusers');
        }
        $issusers=$this->cache->getItem('issusers');

        $configPagSeguro=$this->getTable()->findOneBy(['empresa'=>$issusers['id']]);
        
        if($configPagSeguro->getResult()) {
            $configPagSeguro=$configPagSeguro->getData()->toArray();
           
            if ($this->getData()) {
                $pos = strripos($this->data['title'], ' ');
                if ($pos == true) {
                    $this->data['asset_id'] = 'inscritos';
                    $this->data['empresa'] = $configPagSeguro['id'];
                    $this->data['codigo'] = md5(uniqid(mt_rand(), true));
                    $this->data['sender_hash'] = '';
                    $this->data['created'] = date("Y-m-d");
                    $this->data['modified'] = date("Y-m-d H:i:s");
                    $this->data['cpf'] = str_replace(['.', '-'], '', $this->data['cpf']);
                    $this->data['phone'] = str_replace(['(', ')', '-', '.'], '', $this->data['phone']);
                    $this->table = EventoRepository::class;
                    $this->getTable()->find($this->data['event_id'], false);
                    $event = $this->getTable()->getData()->getData();
                    $submitMessage['email'] = "Nós enviamos para o email:'{$this->data['email']}' as instruções de pagamento da isncrição";
                    $successParagraph[] = "Você tambem pode ligar para:{$issusers['phone']}";
                    $item = ['id' => $event['id'], 'title' => $event['title'], 'qtd' => 1, 'valor' => $event['valor']];
                    $this->data['item'][] = $item;
                    $this->data['PAGSEGURO_ENV'] = $configPagSeguro['ambiente'];
                    $this->data['PAGSEGURO_EMAIL'] = $configPagSeguro['email'];
                    $this->data['PAGSEGURO_TOKEN'] = $configPagSeguro['token'];
                    $this->data['redirecturl'] = $configPagSeguro['redirecturl'];
                    $this->data['notification-url'] = $configPagSeguro['notification'];
                    $this->form = InscritosForm::class;
                    $this->form = $this->getForm();
                    $this->form->setData($this->data);
                    if ($this->form->isValid()) {
                        /**
                         * @var $client Client
                         */
                        $client = $this->containerInterface->get(Client::class);
                        $client->setUri(sprintf("%s/%s", $this->config->serverHost, 'pg/createpayment'));
                        $client->setMethod('POST');
                        $client->setParameterPost($this->data);

                        $response = $client->send();
                        if ($response->isSuccess()) {
                            $this->table = InscritosRepository::class;
                            $this->model = Inscritos::class;
                            $success = json_decode($response->getBody(), true);
                            /**
                             * @var $mode AbstractModel
                             */
                            $model = $this->getModel();
                            $this->data = $this->SigaContas()->decimal($this->data);
                            $model->exchangeArray($this->data);
                            $this->getTable()->insert($model);
                            if ($this->getTable()->getData()->getResult()) {
                                $this->enviaboeto($this->data, $success);
                                $successParagraph[] = "Você tambem pode acessar aqui para efetuar o pagamento :<a class='btn btn-lg btn-success' target='_blank' href='{$success['url']}'>EFETUAR PAGAMENTO</a>";
                                $successParagraph[] = 'E algumas outras informações pertinentes a sua inscrição também foram enviadas!';
                            }

                        } else {
                            $successParagraph = [];
                            $successParagraph[] = 'Oops!, Nenhuma configuração do [PAGSEGURO] foi localizada';
                            $successParagraph[] = $response;
                            $submitMessage = [];
                            $submitMessage[] = 'Oops!, Não foi possivel completar sua inscrição.';
                            $error[] = 'Oops!, Não foi possivel completar sua inscrição.';
                        }
                    } else {
                        $form_msg = $this->form->getMessages();
                        if ($form_msg) {
                            foreach ($form_msg as $key => $msg) {
                                $error[$key] = implode(",", $msg);
                            }
                        }
                    }
                }
                else{
                    $successParagraph=[];
                    $successParagraph[]='Oops!, Nome Invalido';
                    $submitMessage=[];
                    $submitMessage[]='Oops!, Não foi possivel completar sua inscrição.';
                    $error['title']='Oops!, Por Favor Nome E Sobrenome.';
                }


            }

        }
        else{
            $successParagraph=[];
            $successParagraph[]='Oops!, Nenhuma configuração do [PAGSEGURO] foi localizada';
            $submitMessage=[];
            $submitMessage[]='Oops!, Não foi possivel completar sua inscrição.';
            $error[]='Oops!, Não foi possivel completar sua inscrição.';
        }

        $view=new JsonModel(['submitMessage'=>implode("<p>",$submitMessage),
            'successParagraph'=>implode("<p>",$successParagraph),
            'error'=>$error,'success'=>$success,'response'=>json_decode($response, true)]);
        return $view;
    }


    public function notificationAction(){
        if(!$this->cache->hasItem('issusers')){
            $this->containerInterface->get('issusers');
        }
        $issusers=$this->cache->getItem('issusers');

        $configPagSeguro=$this->getTable()->findOneBy(['empresa'=>$issusers['id']]);
        if($configPagSeguro->getResult()) {
            $configPagSeguro=$configPagSeguro->getData()->toArray();

            if($this->getData())
            {
                $this->data['PAGSEGURO_ENV']=$configPagSeguro['ambiente'];
                $this->data['PAGSEGURO_EMAIL']=$configPagSeguro['email'];
                $this->data['PAGSEGURO_TOKEN']=$configPagSeguro['token'];
                /**
                 * @var $client Client
                 */
                $client=$this->containerInterface->get(Client::class);
                $client->setUri(sprintf("%s/%s",$this->config->serverHost,'pg/notification'));
                $client->setMethod('POST');
                $client->setParameterPost($this->data);
                $response = $client->send();
                if ($response->isSuccess()) {
                    $this->table=InscritosRepository::class;
                    $this->model=Inscritos::class;
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
        }
        die;
    }

    protected function enviaboeto($data,$url)
    {
        //PEGAR OS DADOS DO USUARIO RECEM CADASTARDO
        //ADCIONAMOS A URL COM A KEY A VAR DATA
        $data['url'] = $url['url'];
        //PEGAMOS O SERVIÇO DE EMAIL
        $mail = $this->containerInterface->get(Mail::class);
        //SETAMOS AS INFORMAÇÕES DE ENVIO
        //:assunto ->Subject
        //:email do usuario que se cadastro ->To
        //:dados do email ->Data
        //:template de email ->Template
        $mail->setSubject('Link para boleto de inscrição')
            ->setTo($data['email'])
            ->setData($data)
            ->setViewTemplate('inscricao');
        $mail->send();
    }

    public function testAction()
    {


        $haystack = 'aba bcd';
        $needle   = ' ';

        $pos      = strripos($haystack, $needle);

        if ($pos === false) {
            echo "Sinto muito, nós não encontramos ($needle) em ($haystack)";
        } else {
            echo "Parabéns!\n";
            echo "Nós encontramos a última ($needle) em ($haystack) na posição ($pos)";
        }

      die;
    }

}