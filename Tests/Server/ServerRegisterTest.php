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

        $deploymentOptions = array(
            'pre_local_commands' => array('pre_local_command global 1', 'pre_local_command global 2'),
            'post_local_commands' => array('post_local_command global 1', 'post_local_command global 2'),
            'pre_remote_commands' => array('pre_remote_command global 1', 'pre_remote_command global 2'),
            'post_remote_commands' => array('post_remote_command global 1', 'post_remote_command global 2')
        );
        $register->addGlobalOption('deployment', $deploymentOptions);

        // create a server
        $server = new Server('www.test.com', 'webtest', '/home/vagrant', '123123', 22, array(
            'deployment' => array(
                'pre_local_commands' => array(
                    'pre_local_command server specific'
                )
            )
        ));

        // add a server to register
        $register->addServer('srv1', $server);

        $options = $register->getMergedOptions($server);

        $this->assertArrayHasKey('deployment', $options);

        $depOptions = $options['deployment'];
        $this->assertArrayHasKey('pre_local_commands', $depOptions, print_r($depOptions, true));
        $this->assertArrayHasKey('post_local_commands', $depOptions, print_r($depOptions, true));
        $this->assertArrayHasKey('pre_remote_commands', $depOptions, print_r($depOptions, true));
        $this->assertArrayHasKey('post_remote_commands', $depOptions, print_r($depOptions, true));
        $this->assertCount(1, $depOptions['pre_local_commands'], print_r($depOptions['pre_local_commands'], true));
        $this->assertCount(2, $depOptions['post_local_commands'], print_r($depOptions['post_local_commands'], true));
        $this->assertCount(2, $depOptions['pre_remote_commands'], print_r($depOptions['pre_remote_commands'], true));
        $this->assertCount(2, $depOptions['post_remote_commands'], print_r($depOptions['post_remote_commands'], true));
        $this->assertEquals('pre_local_command server specific', $depOptions['pre_local_commands'][0], print_r($depOptions['pre_local_commands'][0], true));
    }
} 