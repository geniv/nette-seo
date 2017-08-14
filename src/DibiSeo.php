<?php

namespace Seo;


/**
 * Class DibiSeo
 *
 * @author  geniv
 * @package Seo
 */
class DibiSeo
{
    use SmartObject;

    // define constant table names
    const
        TABLE_NAME = 'seo';

    /** @var Cache */
    private $cache;
    /** @var Connection database connection from DI */
    private $connection;
    /** @var string table names */
    private $tableSeo;


    /**
     * DibiSeo constructor.
     *
     * @param array      $parameters
     * @param Connection $connection
     * @param IStorage   $storage
     */
    public function __construct(array $parameters, Connection $connection, IStorage $storage)
    {
        $this->connection = $connection;
        $this->cache = new Cache($storage, 'cache-Seo-DibiSeo');
        // define table names
        $this->tableSeo = $parameters['tablePrefix'] . self::TABLE_NAME;
    }

    //TODO save, load DB!!!
}
