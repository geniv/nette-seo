<?php

namespace Seo\Bridges\Nette;

use Nette\DI\CompilerExtension;
use Seo\Seo;


/**
 * Class Extension
 *
 * @author  geniv
 * @package Seo\Bridges\Nette
 */
class Extension extends CompilerExtension
{
    /** @var array default values */
    private $defaults = [
        'tablePrefix' => null,
        'autowired'   => null,
    ];


    /**
     * Load configuration.
     */
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();
        $config = $this->validateConfig($this->defaults);

        // definition seo
        $builder->addDefinition($this->prefix('default'))
            ->setClass(Seo::class, [$config]);

        // if define autowired then set value
        if (isset($config['autowired'])) {
            $builder->getDefinition($this->prefix('default'))
                ->setAutowired($config['autowired']);
        }
    }


    //TODO v pripade zajmu udelat i do ladenky panel ktery bude zobrazovat aktualni pouziti seo title/description
}
