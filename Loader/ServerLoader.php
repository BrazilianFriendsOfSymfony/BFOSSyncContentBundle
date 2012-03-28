<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paulo
 * Date: 3/28/12
 * Time: 5:55 AM
 * To change this template use File | Settings | File Templates.
 */
namespace BFOS\SyncContentBundle\Loader;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

use BFOS\SyncContentBundle\DependencyInjection\ServerConfiguration;
use BFOS\SyncContentBundle\Server\Server;

class ServerLoader implements LoaderInterface
{
    /**
     * Loads the filename
     *
     * @return array
     */
    function load($filename)
    {
        // The aim is not to throw an exception if the file is not found
        if (!file_exists($filename)) {
            return null;
        }

        $configs = Yaml::parse($filename);
        $processor = new Processor();
        $configuration = new ServerConfiguration();

        $servers = $processor->processConfiguration($configuration, $configs);

        $list = array();
        foreach ($servers as $name => $s) {
            $list[$name] = new Server(
                $s['host'],
                $s['user'],
                $s['dir'],
                $s['password'],
                $s['port'],
                $s['options']);
        }

        return $list;
    }

}
