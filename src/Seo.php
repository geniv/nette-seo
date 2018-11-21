<?php declare(strict_types=1);

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
    /** @var Connection */
    private $connection;
    /** @var ILocale */
    private $locale;
    /** @var Application */
    private $application;
    /** @var string */
    private $tableSeo, $tableSeoIdent;
    /** @var bool */
    private $autoCreate = true, $enabled = true;

    /** @var array */
    private $values = [];


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
        // define table names
        $this->tableSeo = $parameters['tablePrefix'] . self::TABLE_NAME;
        $this->tableSeoIdent = $parameters['tablePrefix'] . self::TABLE_NAME_IDENT;

        $this->connection = $connection;
        $this->locale = $locale;
        $this->cache = new Cache($storage, 'Seo-Seo');
        $this->application = $application;

        $this->enabled = boolval($parameters['enabled']);

        $this->loadInternalData();
    }


    /**
     * Set auto create.
     *
     * @param bool $status
     */
    public function setAutoCreate(bool $status)
    {
        $this->autoCreate = $status;
    }


    /**
     * Get item.
     *
     * @internal
     * @param string|null $identification
     * @return array
     */
    private function getItem(string $identification = null): array
    {
        $presenter = $this->application->getPresenter();
        $presenterName = $presenter->getName();
        $presenterAction = $presenter->action;

        if ($identification) {
            $index = $identification . '--';
        } else {
            $index = '-' . $presenterName . '-' . $presenterAction;
        }

        if (isset($this->values[$index])) {
            $item = $this->values[$index];
        } else {
            $item = [];
            $this->saveInternalData($identification, $presenterName, $presenterAction);
        }
        return (array) $item;
    }


    /**
     * Is title.
     *
     * @param string|null $identification
     * @return bool
     */
    public function isTitle(string $identification = null): bool
    {
        $item = $this->getItem($identification);
        return (bool) $item['title'];
    }


    /**
     * Is description.
     *
     * @param string|null $identification
     * @return bool
     */
    public function isDescription(string $identification = null): bool
    {
        $item = $this->getItem($identification);
        return (bool) $item['description'];
    }


    /**
     * Get title.
     *
     * @param string|null $identification
     * @param string|null $default
     * @return string
     */
    public function getTitle(string $identification = null, string $default = null): string
    {
        $item = $this->getItem($identification);
        return $item['title'] ?: $default;
    }


    /**
     * Get description.
     *
     * @param string|null $identification
     * @param string|null $default
     * @return string
     */
    public function getDescription(string $identification = null, string $default = null): string
    {
        $item = $this->getItem($identification);
        return $item['description'] ?: $default;
    }


    /**
     * Render title.
     *
     * @param string|null $identification
     * @param string|null $default
     */
    public function renderTitle(string $identification = null, string $default = null)
    {
        $item = $this->getItem($identification);
        echo $item['title'] ?: $default;
    }


    /**
     * Render description.
     *
     * @param string|null $identification
     * @param string|null $default
     */
    public function renderDescription(string $identification = null, string $default = null)
    {
        $item = $this->getItem($identification);
        echo $item['description'] ?: $default;
    }


    /**
     * Load internal data.
     *
     * @internal
     */
    private function loadInternalData()
    {
        $idLocale = $this->locale->getId();
        $cacheKey = 'loadInternalData' . $idLocale;
        $this->values = $this->cache->load($cacheKey);
        if ($this->values === null) {
            $this->values = $this->connection->select('s.id, si.ident, si.presenter, si.action, ' .
                'CONCAT(IFNULL(si.ident, ""), "-", IFNULL(si.presenter, ""), "-", IFNULL(si.action, "")) assoc, ' .
                's.id_ident, s.id_item, s.title, s.description')
                ->from($this->tableSeoIdent)->as('si')
                ->join($this->tableSeo)->as('s')->on('s.id_ident=si.id')->and(['s.id_locale' => $idLocale])
                ->fetchAssoc('assoc');

            try {
                $this->cache->save($cacheKey, $this->values, [
                    Cache::TAGS => ['loadData'],
                ]);
            } catch (\Throwable $e) {
            }
        }
    }


    /**
     * Save internal data.
     *
     * @internal
     * @param string|null $identification
     * @param string|null $presenter
     * @param string|null $action
     * @throws \Dibi\Exception
     */
    private function saveInternalData(string $identification = null, string $presenter = null, string $action = null)
    {
        if ($identification) {
            $values = ['ident' => $identification];
        } else {
            $values = ['presenter' => $presenter, 'action' => $action];
        }

        $idIdentification = $this->connection->select('id')
            ->from($this->tableSeoIdent)
            ->where($values)
            ->fetchSingle();

        if (!$idIdentification) {
            $idIdentification = $this->connection->insert($this->tableSeoIdent, $values)->execute(Dibi::IDENTIFIER);
        }

        // insert null locale item
        if (!$idIdentification && $this->autoCreate) {
            $idLocale = $this->locale->getId();

            $presenter = $this->application->getPresenter();
            $idItem = $presenter->getParameter('id');

            $this->connection->insert($this->tableSeo, [
                'id_locale' => $idLocale,
                'id_ident'  => $idIdentification,
                'id_item'   => $idItem,
            ])->execute();
        }
    }


//    /**
//     * Internal insert and get id seo by presenter and action.
//     *
//     * @internal
//     * @param string $presenter
//     * @param string $action
//     * @return \Dibi\Result|int|mixed
//     * @throws \Dibi\Exception
//     * @throws \Throwable
//     */
//    private function getIdIdentByPresenterAction(string $presenter, string $action)
//    {
//        $cacheKey = 'getIdIdentByPresenterAction-' . $presenter . '-' . $action;
//        $id = $this->cache->load($cacheKey);
//        if ($id === null) {
//            $id = $this->connection->select('id')
//                ->from($this->tableSeoIdent)
//                ->where(['presenter' => $presenter, 'action' => $action])
//                ->fetchSingle();
//
//            if (!$id) {
//                $id = $this->connection->insert($this->tableSeoIdent, [
//                    'presenter' => $presenter,
//                    'action'    => $action,
//                ])->execute(Dibi::IDENTIFIER);
//            }
//
//            $this->cache->save($cacheKey, $id, [
//                Cache::TAGS => ['seo-cache'],
//            ]);
//        }
//        return $id;
//    }


//    /**
//     * Internal insert and get id seo by ident.
//     *
//     * @internal
//     * @param string $ident
//     * @return \Dibi\Result|int|mixed
//     * @throws \Dibi\Exception
//     * @throws \Throwable
//     */
//    private function getIdIdentByIdent(string $ident)
//    {
//        $cacheKey = 'getIdIdentByIdent-' . $ident;
//        $id = $this->cache->load($cacheKey);
//        if ($id === null) {
//            $values = ['ident' => $ident];
//            $id = $this->connection->select('id')
//                ->from($this->tableSeoIdent)
//                ->where($values)
//                ->fetchSingle();
//
//            // insert new identification if not exist
//            if (!$id) {
//                $id = $this->connection->insert($this->tableSeoIdent, $values)->execute(Dibi::IDENTIFIER);
//            }
//
//            $this->cache->save($cacheKey, $id, [
//                Cache::TAGS => ['seo-cache'],
//            ]);
//        }
//        return $id;
//    }


//    /**
//     * Overloading is and get method.
//     *
//     * @param $name
//     * @param $args
//     * @return mixed
//     * @throws \Dibi\Exception
//     * @throws \Exception
//     * @throws \Throwable
//     */
//    public function __call($name, $args)
//    {
//        if (!in_array($name, ['onAnchor']) && $this->enabled) {   // nesmi zachytavat definovane metody
//            $presenter = $this->application->getPresenter();
//
//            $idLocale = $this->locale->getIdByCode($presenter->getParameter('locale') ?: '');
//            $presenterName = $presenter->getName();
//            $presenterAction = $presenter->action;
//            $idItem = $presenter->getParameter('id');
//
//            $methodName = strtolower(substr($name, 6)); // load method name
//            $ident = (isset($args[0]) ? $args[0] : null);
//            $return = (isset($args[1]) ? $args[1] : false); // echo / return
//
//            // get $idIdent from ident mode or presenter-action mode
//            $idIdent = ($ident ? $this->getIdIdentByIdent($ident) : $this->getIdIdentByPresenterAction($presenterName, $presenterAction));
//
//            // ignore $idItem in case $ident mode
//            if ($ident && $idItem) {
//                $idItem = null;
//            }
//
//            $cacheKey = $name . '-' . $idLocale . '-' . $idIdent . '-' . intval($idItem);
//            $item = $this->cache->load($cacheKey);
//            if ($item === null) {
//                $cursor = $this->connection->select('s.id, s.title, s.description')
//                    ->from($this->tableSeoIdent)->as('si')
//                    ->join($this->tableSeo)->as('s')->on('s.id_ident=si.id')
//                    ->where([
//                        's.id_locale' => $idLocale,
//                        's.id_ident'  => $idIdent,
//                        's.id_item'   => $idItem,
//                    ]);
////                $cursor->test();
//                $item = $cursor->fetch();
//
//                // insert null locale item
//                if (!$item && $this->autoCreate) {
//                    $this->connection->insert($this->tableSeo, [
//                        'id_locale' => $idLocale,
//                        'id_ident'  => $idIdent,
//                        'id_item'   => $idItem,
//                    ])->execute();
//                }
//
//                $this->cache->save($cacheKey, $item, [
//                    Cache::TAGS => ['seo-cache'],
//                ]);
//            }
//
//            // catch is* method
//            switch ($name) {
//                case 'isTitle':
//                case 'getTitle':
//                    return $item['title'];
//                    break;
//
//                case 'isDescription':
//                case 'getDescription':
//                    return $item['description'];
//                    break;
//            }
//
//            $value = $item[$methodName];
//
//            // return value
//            if ($value) {
//                if ($return) {
//                    return $value;
//                } else {
//                    echo $value;
//                }
//            }
//        }
//    }
}
