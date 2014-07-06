<?php

namespace BFOS\SyncContentBundle\Server;

use BFOS\SyncContentBundle\Loader\LoaderInterface;
use BFOS\SyncContentBundle\Server\Server;

class ServerRegister implements ServerRegisterInterface
{
    /**
     * @var array
     */
    protected $servers;

    /**
     * List of options
     * @var array
     */
    protected $options;

    /**
     * Server loader
     * @var \BFOS\SyncContentBundle\Loader\LoaderInterface
     */
    protected $loader;

    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    public function loadServers($filename)
    {
        $servers = $this->loader->load($filename);

        if (null === $servers) {
            return;
        }

        $this->setServers($servers);
    }


    /**
     * Add a server to the list
     *
     * @param string $name
     * @param Server $server
     *
     * @return ServerRegister
     */
    public function addServer($name, $server)
    {
        if (null === $server) {
            throw new \InvalidArgumentException('The server can not be null.');
        }

        if (isset($this->servers[$name])) {
            throw new \InvalidArgumentException(sprintf('The server "%s" is already registered.', $name));
        }

        $this->servers[$name] = $server;

        return $this;
    }

    /**
     * Add a list of servers
     *
     * @param array $servers
     *
     * @return ServerRegister
     */
    public function setServers(array $servers)
    {
        foreach ($servers as $name => $server) {
            $this->addServer($name, $server);
        }

        return $this;
    }

    /**
     * Remove a server
     *
     * @param string $name
     *
     * @return ServerRegister
     */
    public function removeServer($name)
    {
        unset($this->servers[$name]);

        return $this;
    }

    /**
     * Returns a server
     *
     * @param type $server The server name
     *
     * @return \BFOS\SyncContentBundle\Server\ServerInterface
     */
    public function getServer($server)
    {
        if (!isset($this->servers[$server])) {
            throw new \InvalidArgumentException(sprintf('The server "%s" is not registered.', $server));
        }

        return $this->servers[$server];
    }

    /**
     * Returns servers
     *
     * @return array
     */
    public function getServers()
    {
        return $this->servers;
    }

    public function setGlobalOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * Add an option
     *
     * @param string $key
     * @param string $value
     */
    public function addGlobalOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    /**
     * Returns options
     */
    public function getGlobalOptions()
    {
        return $this->options;
    }

}