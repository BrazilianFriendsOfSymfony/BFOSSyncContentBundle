<?php

/*
 * This file is part of the Plum package.
 *
 * (c) 2010-2011 Julien Brochet <mewt@madalynn.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BFOS\SyncContentBundle\Server;

interface ServerInterface
{
    /**
     * Return the connection port
     *
     * @return string
     */
    public function getPort();

    /**
     * Returns the host
     *
     * @return string
     */
    public function getHost();

    /**
     * Returns the directory
     *
     * @return string
     */
    public function getDir();

    /**
     * Returns the user
     *
     * @return string
     */
    public function getUser();

    /**
     * Returns the password
     *
     * @return string
     */
    public function getPassword();

    /**
     * Returns options
     *
     * @return string
     */
    public function getOptions();

    /**
     * Returns the path as in SCP
     *
     * @return string
     */
    public function getPath();
}