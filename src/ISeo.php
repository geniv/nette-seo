<?php declare(strict_types=1);

namespace Seo;


/**
 * Interface ISeo
 *
 * @author  geniv
 * @package Seo
 */
interface ISeo
{

    /**
     * Set auto create.
     *
     * @param bool $status
     */
    public function setAutoCreate(bool $status);


    /**
     * Is title.
     *
     * @param string|null $identification
     * @return bool
     * @throws \Dibi\Exception
     */
    public function isTitle(string $identification = null): bool;


    /**
     * Is description.
     *
     * @param string|null $identification
     * @return bool
     * @throws \Dibi\Exception
     */
    public function isDescription(string $identification = null): bool;


    /**
     * Get title.
     *
     * @param string|null $identification
     * @param string      $default
     * @return string
     * @throws \Dibi\Exception
     */
    public function getTitle(string $identification = null, string $default = ''): string;


    /**
     * Get description.
     *
     * @param string|null $identification
     * @param string      $default
     * @return string
     * @throws \Dibi\Exception
     */
    public function getDescription(string $identification = null, string $default = ''): string;
}
