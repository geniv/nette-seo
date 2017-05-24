<?php

namespace Seo;

use Latte\Runtime\FilterInfo;
use Nette\Application\Application;
use Nette\SmartObject;
use Translator\Translator;


/**
 * Class LatteTitleFilter
 *
 * @author  geniv
 * @package Seo
 */
class LatteTitleFilter
{
    use SmartObject;

    /** @var Translator class */
    private $translator;
    /** @var Application current application */
    private $application;


    /**
     * LatteTitleFilter constructor.
     *
     * @param Translator  $translator
     * @param Application $application
     */
    public function __construct(Translator $translator, Application $application)
    {
        $this->translator = $translator;
        $this->application = $application;
    }


    /**
     * Magic call from template.
     *
     * @param FilterInfo $info
     * @param            $string
     * @return string
     */
    public function __invoke(FilterInfo $info, $string)
    {
        $presenter = $this->application->getPresenter();

        $parameters = $presenter->getParameters();
        $ident = 'seo-title-' . $presenter->getName() . '-' . $presenter->getAction() . (isset($parameters['id']) ? '-' . $parameters['id'] : '');

        $translate = $this->translator->createTranslate($ident, $string ?: $ident);

        return $translate;
    }
}
