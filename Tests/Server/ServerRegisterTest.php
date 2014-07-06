<?php
namespace BFOS\SyncContentBundle\Tests\Command;


use BFOS\SyncContentBundle\Loader\ServerLoader;
use BFOS\SyncContentBundle\Server\Server;
use BFOS\SyncContentBundle\Server\ServerRegister;

class ServerRegisterTest extends \PHPUnit_Framework_TestCase
{
    public function testAddServerToServersListSuccessfully()
    {
        // create a server register
        $register = new ServerRegister(new ServerLoader());

        // create a server
        $server = new Server('www.test.com', 'webtest', '/home/vagrant', '123123');

        // add a server to register
        $register->addServer('srv1', $server);

        // test the server was added
        $this->assertEquals(1, count($register->getServers()), 'Server not added.');
    }
} 