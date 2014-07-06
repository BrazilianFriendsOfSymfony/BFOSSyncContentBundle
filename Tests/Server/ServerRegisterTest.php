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

    public function testGetMergedOptionsMergesSuccessfully()
    {
        // create a server register
        $register = new ServerRegister(new ServerLoader());

        $register->addGlobalOption('pre_local_commands', array('pre_local_command global 1', 'pre_local_command global 2'));
        $register->addGlobalOption('post_local_commands', array('post_local_command global 1', 'post_local_command global 2'));
        $register->addGlobalOption('pre_remote_commands', array('pre_remote_command global 1', 'pre_remote_command global 2'));
        $register->addGlobalOption('post_remote_commands', array('post_remote_command global 1', 'post_remote_command global 2'));

        // create a server
        $server = new Server('www.test.com', 'webtest', '/home/vagrant', '123123', 22, array(
            'pre_local_commands' => array(
                'pre_local_command server specific'
            )
        ));

        // add a server to register
        $register->addServer('srv1', $server);

        $options = $register->getMergedOptions($server);

        $this->assertArrayHasKey('pre_local_commands', $options);
        $this->assertArrayHasKey('post_local_commands', $options);
        $this->assertArrayHasKey('pre_remote_commands', $options);
        $this->assertArrayHasKey('post_remote_commands', $options);
        $this->assertCount(1, $options['pre_local_commands']);
        $this->assertCount(2, $options['post_local_commands']);
        $this->assertCount(2, $options['pre_remote_commands']);
        $this->assertCount(2, $options['post_remote_commands']);
        $this->assertEquals('pre_local_command server specific', $options['pre_local_commands'][0]);
    }
} 