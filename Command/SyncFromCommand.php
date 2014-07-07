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

use BFOS\SyncContentBundle\Server\ServerRegisterInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncFromCommand extends AbstractSyncCommand
{
    protected function configure()
    {
        $this
            ->setName('bfos:sync-content:from')
            ->setDescription('Synchronize content FROM another server')
            ->addArgument('remoteenv', InputArgument::REQUIRED, 'The environment name and server name concatenated by @ .')
            ->addOption('only-database', null, InputOption::VALUE_OPTIONAL, 'Indicates that only the database content should be synchronized.', false)
            ->setHelp(<<<EOF
The <info>bfos:sync-content:from</info> command synchronize the content from a remote server to your computer:

  <info>php app/console bfos:sync-content:from prod@production</info>

The server must be configured in <comment>app/config/deployment_sync_content.yml</comment>:

    servers:
        production:
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
         * @var ServerRegisterInterface $register
         */
        $register = $this->getContainer()->get('bfos_sync_content.server_register');

        $server = $register->getServer($serverName);

        // Synchronize the Mysql database

        $cmd_local = "php app/console bfos:sync-content:mysql-load --env=".$localEnv;
        $cmd_remote = sprintf("php app/console bfos:sync-content:mysql-dump --env=".$remoteEnv);

//        $cmd = sprintf("php app/console bfos:sync-content:mysql-dump | ssh -p %d %s@%s ", $server->getPort(), $server->getUser(), $server->getHost());
        $cmd = sprintf("ssh -p %d %s@%s ", $server->getPort(), $server->getUser(), $server->getHost());
        $cmd .= escapeshellarg("(cd " . escapeshellarg($server->getDir()) . "; " . $cmd_remote . " )");
        $cmd .= " | " . $cmd_local;

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
            $this->synchronize_content('from', $server, $register, $output);
        }



        $output->writeln('Synchronization was successful.');
        return true;

    }

}

