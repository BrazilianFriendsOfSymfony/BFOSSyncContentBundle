<?php
 /**
 * This file is part of the Duo Criativa software.
 * Este arquivo é parte do software da Duo Criativa.
 *
 * (c) Paulo Ribeiro <paulo@duocriativa.com.br>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace BFOS\SyncContentBundle\Tests\Command;


use BFOS\SyncContentBundle\Command\ShowServerCommand;
use BFOS\SyncContentBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ShowServerCommandTest extends \PHPUnit_Framework_TestCase {
    public function testExecute()
    {
        $application = new Application(new AppKernel('test', false));
        $application->add(new ShowServerCommand());

        $command = $application->find('bfos:sync-content:show-server');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

        $this->assertRegExp('/.../', $commandTester->getDisplay());

        // ...
    }
} 