<?php

/*
 * This file is part of the Madalynn package.
 *
 * (c) 2010-2011 Julien Brochet <mewt@madalynn.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BFOS\SyncContentBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use \BFOS\SyncContentBundle\Server\Server;

class SyncToCommand extends AbstractSyncCommand
{
    protected function configure()
    {
        $this
            ->setName('bfos:sync-content:to')
            ->setDescription('Synchronize content TO another server')
            ->addArgument('remoteenv', InputArgument::REQUIRED, 'The environment name and server name concatenated by @ .')
            ->addOption('only-database', null, InputOption::VALUE_OPTIONAL, 'Indicates that only the database content should be synchronized.', false)
            ->setHelp(<<<EOF
The <info>bosf:sync-content:to</info> command synchronize the content from your computer to a remote server:

  <info>php app/console bfos:sync-content:to prod@staging</info>

The server must be configured in <comment>app/config/deployment_sync_content.yml</comment>:

    servers:
        staging:
            host: www.mywebsite.com
            port: 22
            user: julien
            dir: /var/www/sfblog/

To automate the synchronization, the task uses rsync over SSH.
You must configure SSH access with a key or configure the password
in <comment>app/config/deployment_sync_content.yml</comment>.
EOF
            )
        ;
    }



    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $remoteEnv    = $input->getArgument('remoteenv');
        $remoteEnv = $this->checkRemoveEnvParam($remoteEnv);

        $serverName = $remoteEnv['server'];
        $remoteEnv = $remoteEnv['env'];

        $localEnv = $input->getOption('env');

        /**
         * @var \BFOS\SyncContentBundle\Server\ServerRegisterInterface $register
         */
        $register = $this->getContainer()->get('bfos_sync_content.server_register');

        $server = $register->getServer($serverName);

        // Synchronize the Mysql database

        $cmd_local = "php app/console bfos:sync-content:mysql-dump --env=".$localEnv;
        $cmd_remote = "php app/console bfos:sync-content:mysql-load --env=".$remoteEnv;

        $cmd = sprintf($cmd_local. " | ssh -p %d %s@%s ", $server->getPort(), $server->getUser(), $server->getHost());
        $cmd .= escapeshellarg("(cd " . escapeshellarg($server->getDir()) . "; " . $cmd_remote . " )");

        $output->writeln('Synchronizing Mysql database...');
        $output->writeln($cmd);

        // Right to stdout for the convenience of the remote ssh connection from
        // sync-content
        system($cmd, $result);

        if ($result != 0)
        {
            throw new \Exception("Mysql synchronization failed");
        }
        $output->writeln('Mysql database synchronized successfully.');

        // END - mysql

        if(!$input->getOption('only-database')){
            $this->synchronize_content('to', $server, $register, $output);
        }

        $output->writeln('Synchronization was successful.');
        return true;

    }

}

