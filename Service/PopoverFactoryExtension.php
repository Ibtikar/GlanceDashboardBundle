<?php
namespace Ibtikar\GlanceDashboardBundle\Service;

/**
 * Description of PopoverFactoryExtension
 *
 * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
 */
class PopoverFactoryExtension extends \Twig_Extension
{

    private $translator;
    private $popoverDefaultConfig;

    public function __construct($translator) {
        $this->translator = $translator;

        $this->popoverDefaultConfig = array(
            "question" => "You are about to delete (%title%),Are you sure?",
            "replaceAttr" => false,
            "buttons" => array(
                [
                    "text" => "Yes",
                    "class" => "dev-delete-btn btn-danger",
                    "callback" => "callUrl",
                    "callback-param" => [
                        "data-href"
                    ]
                ],
                [
                    "text" => "Cancel",
                    "class" => "btn-defualt",
                ],
            ),
            "translationDomain" => null,
        );

    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'popover_factory', array($this, 'popoverFactory')
            )
        );
    }

    public function popoverFactory($popoverConfig = array())
    {
        $popoverConfig = array_merge($this->popoverDefaultConfig, $popoverConfig);

        $popOverAttrs = 'role="button" tabindex="0" data-toggle="popover" data-popup="popover" data-trigger="focus" data-html="true" data-content="';

        foreach ($popoverConfig['buttons'] as $button){
            $popOverAttrs .= '<button type=\'button\' class=\'btn '. (isset($button['class'])?$button['class']:"") .'\'>' .$this->translator->trans(isset($button['text'])?$button['text']:"",array(),$popoverConfig["translationDomain"]). '</button>';
        }

        $popOverAttrs .= ($popoverConfig['replaceAttr']?'" data-replace-title="'.$this->translator->trans($popoverConfig["question"],array(),$popoverConfig["translationDomain"]):"").'" data-original-title="'.$this->translator->trans($popoverConfig["question"],array(),$popoverConfig["translationDomain"]).'"';

        return $popOverAttrs;
    }

}