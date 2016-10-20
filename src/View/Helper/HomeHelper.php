<?php
/**
 * Created by PhpStorm.
 * User: claudio
 * Date: 01/09/2016
 * Time: 08:27
 */

namespace Home\View\Helper;


use Base\Model\Cache;
use Interop\Container\ContainerInterface;
use Mail\Form\MailForm;
use Suricatis\Form\InscritosForm;
use Zend\View\Helper\AbstractHelper;
use Admin\Model\Facebook\FacebookRepository;

class HomeHelper extends AbstractHelper {

    protected static  $labels;
    protected static $html;
    protected static $social=['facebook'=>FacebookRepository::class];
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container){

        $this->container = $container;
        $this->config=$this->container->get("ZfConfig");
    }

    public function setSeo($tabela,$param=['state'=>'0']){
        $seo=$this->container->get(self::$social[$tabela])->findOneBy($param);
        if($seo->getResult()){
            return $seo->getData();
        }
        return null;
    }

    public function getIssusers($param=''){
        $Issusers=$this->container->get(Cache::class);
        if(!$Issusers->hasItem('issusers')){
            $this->container->get('issusers');
        }
        if(empty($param)){
            return $Issusers->getItem('issusers');
        }

        return $Issusers->getItem('issusers')[$param];

    }



    public function formContato($html){
        echo  $this->view->messages();
        $form = $this->container->get(MailForm::class);
        $form->setAttribute('action', $this->view->url('callcocam', array('controller' => 'callcocam', 'action' => 'contact')));
        $form->setAttribute('id', 'main-contact-form');
        $form->setAttribute('class', 'contact-form');
        $this->view->formElementErrors()
            ->setMessageOpenFormat('<ul class="nav"><li class="erro-obrigatorio">')
            ->setMessageSeparatorString('</li>')->render($form);
        $formRender[]= $this->view->form()->openTag($form);

        $this->GerarElement($form,false);


        $primeiro = str_replace(array_keys(self::$html), array_values(self::$html), $html);
        $formRender[]= str_replace(array_keys(self::$labels), array_values(self::$labels), $primeiro);
        $formRender[]= $this->view->form()->closeTag();
        return implode("",$formRender);

    }

    public function formIncrevase($html,$id=0){
        echo  $this->view->messages();

        $form = $this->container->get(InscritosForm::class);
        $form->setAttribute('action', $this->view->url('home/default', array('controller' => 'home', 'action' => 'inscricao')));
        $form->setAttribute('id', 'register');
        $this->view->formElementErrors()
            ->setMessageOpenFormat('<ul class="nav"><li class="erro-obrigatorio">')
            ->setMessageSeparatorString('</li>')->render($form);
        $formRender[]= $this->view->form()->openTag($form);
        $form->get('access')->setAttribute('type','hidden')->setValue(3);
        $form->get('state')->setAttribute('type','hidden')->setValue(1);
        $form->get('event_id')->setAttribute('type','hidden')->setValue($id);
        $form->get('created')->setAttribute('type','hidden');
        $form->get('uf')->setAttribute('type','hidden')->setValue("SC");
        $form->get('complemento')->setAttribute('type','hidden')->setValue("casa");
        $this->GerarElement($form,false);
        $primeiro = str_replace(array_keys(self::$html), array_values(self::$html), $html);
        $formRender[]= str_replace(array_keys(self::$labels), array_values(self::$labels), $primeiro);
        $formRender[]= $this->view->form()->closeTag();
        return implode("",$formRender);

    }

    public function GerarElement($form,$removeClass=true) {
        foreach ($form->getElements() as $key => $element) {
            $visible = "";
            //verifica se o usuario pode ter acesso ao campo
            if ($element->hasAttribute('placeholder')) {
                $element->setAttribute('placeholder', $this->view->translate($element->getAttribute('placeholder')));
            }
            if($removeClass){
                $element->setAttribute('class','');
            }

            if (!empty($element->getLabel())) {
                self::$labels["{{{$key}}}"] = $this->view->Html("label")->setAttributes(array("class" => "field-label", "for" => $element->getName()))->setText($this->view->translate($element->getLabel()));
            }
            // verifica se e um campo hidden [oculto]
            if ($element->getAttribute('type') === "hidden") {
                self::$html["#{$key}#"] = $this->view->formHidden($element->setLabel(''));
            } elseif ($element->getAttribute('type') === "submit") {
                $element->setValue("INSCREVER-SE");
                self::$html["#{$key}#"] = $this->view->formSubmit($element);
            } elseif ($element->getAttribute('type') === "radio") {
                self:: $html["#{$key}#"] = $this->radio($element);
            } elseif ($element->getAttribute('name') === "images") {
                $base_path = $this->container->get('request')->getServer('DOCUMENT_ROOT');
                if (!is_file(sprintf("%s%sdist%s%s", $base_path, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $element->getValue()))):
                    $caminho = "no_avatar.jpg";
                else:
                    $caminho = $element->getValue();
                endif;
                self::$html["#imagePreview#"] = \Base\Model\Check::Image($caminho, $element->getValue(), "420", "330", "thumbnail img-responsive preview_IMG");
                $element->setAttribute('type', 'hidden')->setLabel("");
                self::$html["#{$key}#"] = $this->view->formHidden($element);
            } else {
                self::$html["#{$key}#"] = $this->view->formRow($element->setLabel(''));
            }
        }

    }

} 