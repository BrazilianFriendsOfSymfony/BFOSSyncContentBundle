<?php

namespace BFOS\SyncContentBundle\Synchronization;


use BFOS\SyncContentBundle\Server\ServerInterface;

interface SynchronizerInterface
{
    /**
     * Deploy to the server using the deployer
     *
     * @param ServerInterface $server
     * @param array $options
     */
    public function synchronize(ServerInterface $server, $options = array());
}
