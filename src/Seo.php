<?php

namespace Seo;

use dibi;
use Dibi\Connection;
use Locale\Locale;
use Nette\Application\Application;
use Nette\Application\UI\Control;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;


/**
 * Class Seo
 *
 * @author  geniv
 * @package Seo
 */
class Seo extends Control
{
    // define constant table names
    const
        TABLE_NAME = 'seo',
        TABLE_NAME_IDENT = 'seo_ident';

    /** @var Cache */
    private $cache;
    /** @var Connection database connection from DI */
    private $connection;
    /** @var Locale */
    private $locale;
    /** @var Application */
    private $application;
    /** @var string table names */
    private $tableSeo, $tableSeoIdent;

    private $separator;


    /**
     * Seo constructor.
     *
     * @param array       $parameters
     * @param Connection  $connection
     * @param Locale      $locale
     * @param IStorage    $storage
     * @param Application $application
     */
    public function __construct(array $parameters, Connection $connection, Locale $locale, IStorage $storage, Application $application)
    {
        $this->connection = $connection;
        $this->locale = $locale;
        $this->cache = new Cache($storage, 'cache-Seo-Seo');
        $this->application = $application;
        // define table names
        $this->tableSeo = $parameters['tablePrefix'] . self::TABLE_NAME;
        $this->tableSeoIdent = $parameters['tablePrefix'] . self::TABLE_NAME_IDENT;

        $this->separator = [
            'prefix' => $parameters['prefixSeparator'],
            'suffix' => $parameters['suffixSeparator'],
        ];
    }


    /**
     * Internal insert and get id seo by presenter and action.
     *
     * @param $presenter
     * @param $action
     * @return mixed
     */
    private function getIdent($presenter, $action)
    {
        $cacheKey = 'getIdSeo-' . $presenter . '-' . $action;
        $id = $this->cache->load($cacheKey);
        if ($id === null) {
            $id = $this->connection->select('id')
                ->from($this->tableSeoIdent)
                ->where(['presenter' => $presenter, 'action' => $action])
                ->fetchSingle();

            if (!$id) {
                $id = $this->connection->insert($this->tableSeoIdent, [
                    'presenter' => $presenter,
                    'action'    => $action,
                ])->execute(Dibi::IDENTIFIER);
            }

            $this->cache->save($cacheKey, $id, [
                Cache::TAGS => ['seo-cache'],
            ]);
        }
        return $id;
    }


    /**
     * Internal insert and get id seo by ident.
     *
     * @param $ident
     * @return mixed
     */
    private function getIdentByIdent($ident)
    {
        $cacheKey = 'getIdentByIdent-' . $ident;
        $id = $this->cache->load($cacheKey);
        if ($id === null) {
            $id = $this->connection->select('id')
                ->from($this->tableSeoIdent)
                ->where(['ident' => $ident])
                ->fetchSingle();

            if (!$id) {
                $id = $this->connection->insert($this->tableSeoIdent, [
                    'ident' => $ident,
                ])->execute(Dibi::IDENTIFIER);
            }

            $this->cache->save($cacheKey, $id, [
                Cache::TAGS => ['seo-cache'],
            ]);
        }
        return $id;
    }


    /**
     * Overloading is and get method.
     *
     * LATTE:
     * {control seo:title}
     * {control seo:description}
     * {control seo:title 'default-latte'}
     * {control seo:description 'default-latte'}
     * return usage: {control seo:description 'default-latte', true}
     * {if $presenter['seo']->isTitle()}
     * {if $presenter['seo']->isDescription()}
     *
     * @param $name
     * @param $args
     * @return mixed
     */
    public function __call($name, $args)
    {
        if (!in_array($name, ['onAnchor'])) {   // nesmi zachytavat definovane metody
            $presenter = $this->application->getPresenter();

            $idLocale = $this->locale->getIdByCode($presenter->getParameter('locale'));
            $presenterName = $presenter->getName();
            $presenterAction = $presenter->action;
            $idItem = $presenter->getParameter('id');

            $methodName = strtolower(substr($name, 6)); // load method name
            $ident = (isset($args[0]) ? $args[0] : null);
            $return = (isset($args[1]) ? $args[1] : false); // echo / return

            // get $idIdent from ident mode or presenter-action mode
            $idIdent = ($ident ? $this->getIdentByIdent($ident) : $this->getIdent($presenterName, $presenterAction));

            // ignore $idItem in case $ident mode
            if ($idIdent && $idItem) {
                $idItem = null;
            }

            $values = [
                's.id'        => $idIdent,
                'tab.id_item' => $idItem,
            ];

            $cacheKey = $name . '-' . $idLocale . '-' . $idIdent . '-' . intval($idItem);
            $item = $this->cache->load($cacheKey);
            if ($item === null) {
                $cursor = $this->connection->select('s.id, tab.id tid, ' .
                    'IFNULL(lo_tab.title, tab.title) title, ' .
                    'IFNULL(lo_tab.description, tab.description) description')
                    ->from($this->tableSeoIdent)->as('s')
                    ->leftJoin($this->tableSeo)->as('tab')->on('tab.id_ident=s.id')->and('tab.id_locale IS NULL')
                    ->leftJoin($this->tableSeo)->as('lo_tab')->on('lo_tab.id_ident=s.id')->and('lo_tab.id_locale=%i', $idLocale)
                    ->where($values);

//                $cursor->test();
                $item = $cursor->fetch();

                $this->cache->save($cacheKey, $item, [
                    Cache::TAGS => ['seo-cache'],
                ]);
            }

            // insert null locale item
            if (!$item['tid']) {
                $this->connection->insert($this->tableSeo, [
                    'id_locale' => null,
                    'id_ident'  => $idIdent,
                    'id_item'   => $idItem,
                ])->execute();
            }

            // catch is* method
            switch ($name) {
                case 'isTitle':
                    return $item['title'];
                    break;

                case 'isDescription':
                    return $item['description'];
                    break;
            }

            $value = $item[$methodName];

            // before separator
            if (isset($this->separator['prefix'][$ident])) {
                $value = $this->separator['prefix'][$ident] . $value;
            }
            // after separator
            if (isset($this->separator['suffix'][$ident])) {
                $value = $value . $this->separator['suffix'][$ident];
            }

            // return value
            if ($value) {
                if ($return) {
                    return $value;
                } else {
                    echo $value;
                }
            }
        }
    }
}
