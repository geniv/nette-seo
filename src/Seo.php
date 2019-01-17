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
class Seo extends Control implements ISeo
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
     * @throws \Dibi\Exception
     */
    private function getItem(string $identification = null): array
    {
        if (!$this->enabled) {
            return [];
        }

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
     * @throws \Dibi\Exception
     */
    public function isTitle(string $identification = null): bool
    {
        return (bool) $this->getTitle($identification);
    }


    /**
     * Is description.
     *
     * @param string|null $identification
     * @return bool
     * @throws \Dibi\Exception
     */
    public function isDescription(string $identification = null): bool
    {
        return (bool) $this->getDescription($identification);
    }


    /**
     * Get title.
     *
     * @param string|null $identification
     * @param string      $default
     * @return string
     * @throws \Dibi\Exception
     */
    public function getTitle(string $identification = null, string $default = ''): string
    {
        $item = $this->getItem($identification);
        return $item['title'] ?? $default;
    }


    /**
     * Get description.
     *
     * @param string|null $identification
     * @param string      $default
     * @return string
     * @throws \Dibi\Exception
     */
    public function getDescription(string $identification = null, string $default = ''): string
    {
        $item = $this->getItem($identification);
        return $item['description'] ?? $default;
    }


    /**
     * Render title.
     *
     * @param string|null $identification
     * @param string      $default
     * @throws \Dibi\Exception
     */
    public function renderTitle(string $identification = null, string $default = '')
    {
        echo $this->getTitle($identification, $default);
    }


    /**
     * Render description.
     *
     * @param string|null $identification
     * @param string      $default
     * @throws \Dibi\Exception
     */
    public function renderDescription(string $identification = null, string $default = '')
    {
        echo $this->getDescription($identification, $default);
    }


    /**
     * Load internal data.
     *
     * @internal
     */
    private function loadInternalData()
    {
        // skip load data
        if (!$this->enabled) {
            return;
        }

        $idLocale = $this->locale->getId();
        $cacheKey = 'loadInternalData' . $idLocale;
        $this->values = $this->cache->load($cacheKey);
        if ($this->values === null) {
            $this->values = $this->connection->select('s.id, si.ident, si.presenter, si.action, ' .
                'CONCAT(IFNULL(si.ident,""), "-", IFNULL(si.presenter,""), "-", IFNULL(si.action,"")) uid, ' .
                's.id_ident, s.id_item, s.title, s.description')
                ->from($this->tableSeoIdent)->as('si')
                ->join($this->tableSeo)->as('s')->on('s.id_ident=si.id')->and(['s.id_locale' => $idLocale])
                ->fetchAssoc('uid');

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

        $idLocale = $this->locale->getId();
        $presenter = $this->application->getPresenter();
        $idItem = $presenter->getParameter('id');
        $val = [
            'id_locale' => $idLocale,
            'id_ident'  => $idIdentification,
            'id_item'   => $idItem,
        ];

        $item = $this->connection->select('id')
            ->from($this->tableSeo)
            ->where($val)
            ->fetchSingle();

        // insert null locale item
        if (!$item && $this->autoCreate) {
            $this->connection->insert($this->tableSeo, $val)->execute(Dibi::IDENTIFIER);
        }

        $this->cache->clean([Cache::TAGS => ['loadData']]);
    }
}
