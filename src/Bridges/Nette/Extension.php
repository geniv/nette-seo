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
        $config = $this->getConfig();

        // pripojeni pro seo rozsireni
//        if (isset($config['parameters']['seo']) && $config['parameters']['seo']) {
        // nacteni filteru
        $builder->addDefinition($this->prefix('filter.title'))
            ->setClass(LatteTitleFilter::class)
            ->setInject(false);

        $builder->addDefinition($this->prefix('filter.description'))
            ->setClass(LatteDescriptionFilter::class)
            ->setInject(false);
//        }

        // pripojeni filru na vkladani slugu
        $latte = $builder->getDefinition('nette.latteFactory');

        // pripojeni pro seo rozsireni
        $latte->addSetup('addFilter', ['seoTitle', $this->prefix('@filter.title')]);
        $latte->addSetup('addFilter', ['seoDescription', $this->prefix('@filter.description')]);
    }
}
