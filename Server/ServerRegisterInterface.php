<?php
namespace BFOS\SyncContentBundle\Server;

use Doctrine\Common\Annotations\Annotation\Attribute;

interface ServerRegisterInterface
{
    public function loadServers($filename);

    /**
     * Add a server to the list
     *
     * @param string $name
     * @param ServerInterface $server
     *
     * @return ServerRegisterInterface
     */
    public function addServer($name, ServerInterface $server);

    /**
     * Add a list of servers
     *
     * @param array $servers
     *
     * @return ServerRegisterInterface
     */
    public function setServers(array $servers);

    /**
     * Remove a server
     *
     * @param string $name
     *
     * @return ServerRegisterInterface
     */
    public function removeServer($name);

    /**
     * Returns a server
     *
     * @param string $server The server name
     *
     * @return ServerInterface
     */
    public function getServer($server);

    /**
     * Returns servers
     *
     * @return array
     */
    public function getServers();

    /**
     * @param array $options
     *
     * @return ServerRegisterInterface
     */
    public function setGlobalOptions(array $options);

    /**
     * Add an option
     *
     * @param string $key
     * @param string $value
     */
    public function addGlobalOption($key, $value);

    /**
     * Returns options
     */
    public function getGlobalOptions();

    /**
     * Returns the server options merged with the global ones.
     *
     * @param ServerInterface $server
     * @return array
     */
    public function getMergedOptions(ServerInterface $server);
}