<?php

namespace Seo\Bridges\Nette;

use Seo\LatteDescriptionFilter;
use Seo\LatteTitleFilter;
use Nette\DI\CompilerExtension;


/**
 * Class Extension
 *
 * nette extension pro seo jako rozsireni
 *
 * @author  geniv
 * @package Seo\Bridges\Nette
 */
class Extension extends CompilerExtension
{

    /**
     * Load configuration.
     */
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();
        //$config = $this->getConfig();

        // nacteni filteru
        $builder->addDefinition($this->prefix('filter.title'))
            ->setClass(LatteTitleFilter::class);

        $builder->addDefinition($this->prefix('filter.description'))
            ->setClass(LatteDescriptionFilter::class);

        // pripojeni filru na vkladani slugu
        $builder->getDefinition('latte.latteFactory')
            ->addSetup('addFilter', ['seoTitle', $this->prefix('@filter.title')])
            ->addSetup('addFilter', ['seoDescription', $this->prefix('@filter.description')]);
    }
}
