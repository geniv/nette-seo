<?php

namespace Seo;

use Latte\Runtime\FilterInfo;
use Nette\Application\Application;
use Nette\SmartObject;
use Translator\Translator;


/**
 * Class FilterTitle
 *
 * @author  geniv
 * @package Seo
 */
class FilterTitle
{
    use SmartObject;

    /** @var Translator class */
    private $translator;


    /**
     * FilterTitle constructor.
     *
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
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
        return $this->translator->translate($string);
    }
}
