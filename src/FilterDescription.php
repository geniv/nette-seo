<?php

namespace Seo;

use Latte\Runtime\FilterInfo;
use Nette\Application\Application;
use Nette\SmartObject;
use Translator\Translator;


/**
 * Class FilterDescription
 *
 * @author  geniv
 * @package Seo
 */
class FilterDescription
{
    use SmartObject;

    /** @var Translator class */
    private $translator;


    /**
     * FilterDescription constructor.
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
