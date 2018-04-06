<?php

namespace Seo;

use dibi;
use Dibi\Connection;
use Locale\ILocale;
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
    /** @var ILocale */
    private $locale;
    /** @var Application */
    private $application;
    /** @var string table names */
    private $tableSeo, $tableSeoIdent;
    /** @var bool */
    private $autoCreate = true, $enabled = true;


    /**
     * Seo constructor.
     *
     * @param array       $parameters
     * @param Connection  $connection
     * @param ILocale     $locale
     * @param IStorage    $storage
     * @param Application $application
     */
    public function __construct(array $parameters, Connection $connection, ILocale $locale, IStorage $storage, Application $application)
    {
        $this->connection = $connection;
        $this->locale = $locale;
        $this->cache = new Cache($storage, 'cache-Seo-Seo');
        $this->application = $application;
        // define table names
        $this->tableSeo = $parameters['tablePrefix'] . self::TABLE_NAME;
        $this->tableSeoIdent = $parameters['tablePrefix'] . self::TABLE_NAME_IDENT;

        $this->enabled = boolval($parameters['enabled']);
    }


    /**
     * Set auto create.
     *
     * @param bool $status
     * @return Seo
     */
    public function setAutoCreate($status)
    {
        $this->autoCreate = $status;
        return $this;
    }


    /**
     * Internal insert and get id seo by presenter and action.
     *
     * @param $presenter
     * @param $action
     * @return mixed
     * @throws \Dibi\Exception
     * @throws \Exception
     * @throws \Throwable
     */
    private function getIdIdentByPresenterAction($presenter, $action)
    {
        $cacheKey = 'getIdIdentByPresenterAction-' . $presenter . '-' . $action;
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
     * @throws \Dibi\Exception
     * @throws \Exception
     * @throws \Throwable
     */
    private function getIdIdentByIdent($ident)
    {
        $cacheKey = 'getIdIdentByIdent-' . $ident;
        $id = $this->cache->load($cacheKey);
        if ($id === null) {
            $values = ['ident' => $ident];
            $id = $this->connection->select('id')
                ->from($this->tableSeoIdent)
                ->where($values)
                ->fetchSingle();

            // insert new identification if not exist
            if (!$id) {
                $id = $this->connection->insert($this->tableSeoIdent, $values)->execute(Dibi::IDENTIFIER);
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
     * @param $name
     * @param $args
     * @return mixed
     * @throws \Dibi\Exception
     * @throws \Exception
     * @throws \Throwable
     */
    public function __call($name, $args)
    {
        if (!in_array($name, ['onAnchor']) && $this->enabled) {   // nesmi zachytavat definovane metody
            $presenter = $this->application->getPresenter();

            $idLocale = $this->locale->getIdByCode($presenter->getParameter('locale') ?: '');
            $presenterName = $presenter->getName();
            $presenterAction = $presenter->action;
            $idItem = $presenter->getParameter('id');

            $methodName = strtolower(substr($name, 6)); // load method name
            $ident = (isset($args[0]) ? $args[0] : null);
            $return = (isset($args[1]) ? $args[1] : false); // echo / return

            // get $idIdent from ident mode or presenter-action mode
            $idIdent = ($ident ? $this->getIdIdentByIdent($ident) : $this->getIdIdentByPresenterAction($presenterName, $presenterAction));

            // ignore $idItem in case $ident mode
            if ($ident && $idItem) {
                $idItem = null;
            }

            $cacheKey = $name . '-' . $idLocale . '-' . $idIdent . '-' . intval($idItem);
            $item = $this->cache->load($cacheKey);
            if ($item === null) {
                $cursor = $this->connection->select('s.id, s.title, s.description')
                    ->from($this->tableSeoIdent)->as('si')
                    ->join($this->tableSeo)->as('s')->on('s.id_ident=si.id')
                    ->where([
                        's.id_locale' => $idLocale,
                        's.id_ident'  => $idIdent,
                        's.id_item'   => $idItem,
                    ]);
//                $cursor->test();
                $item = $cursor->fetch();

                // insert null locale item
                if (!$item && $this->autoCreate) {
                    $this->connection->insert($this->tableSeo, [
                        'id_locale' => $idLocale,
                        'id_ident'  => $idIdent,
                        'id_item'   => $idItem,
                    ])->execute();
                }

                $this->cache->save($cacheKey, $item, [
                    Cache::TAGS => ['seo-cache'],
                ]);
            }

            // catch is* method
            switch ($name) {
                case 'isTitle':
                case 'getTitle':
                    return $item['title'];
                    break;

                case 'isDescription':
                case 'getDescription':
                    return $item['description'];
                    break;
            }

            $value = $item[$methodName];

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
