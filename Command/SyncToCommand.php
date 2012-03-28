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

class SyncToCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bfos:sync-content:to')
            ->setDescription('Synchronize content TO another server')
            ->addArgument('remoteenv', InputArgument::REQUIRED, 'The environment name and server name concatenated by @ .')
//            ->addOption('env', null, InputOption::VALUE_OPTIONAL, 'The local environment name to be used.', 'dev')
            ->setHelp(<<<EOF
The <info>bosf:sync-content:to</info> command synchronize the content from your computer to a remote server:

  <info>php app/console bfos:sync-content:to prod@production</info>

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

    protected function checkRemoveEnvParam($remoteenv){
        if (!preg_match('/^(.*)\@(.*)?$/', $remoteenv, $matches))
        {
            throw new sfException("remoteenv must be of the form environment@site, example: dev@staging or prod@production; the site must be defined in app/config/deployment_sync_content.yml");
        }
        $envRemote = $matches[1];
        $server = $matches[2];
        return array('env'=>$envRemote, 'server'=>$server);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $remoteenv    = $input->getArgument('remoteenv');
        $remoteenv = $this->checkRemoveEnvParam($remoteenv);

        $envRemote = $remoteenv['env'];
        $servername = $remoteenv['server'];

        /**
         * @var \BFOS\SyncContentBundle\Manager $manager
         */
        $manager = $this->getContainer()->get('bfos_sync_content.manager');

        $server = $manager->getServer($servername);

        // Synchronize the Mysql database

        $cmd_remote = "php app/console bfos:sync-content:mysql-load";

        $cmd = sprintf("php app/console bfos:sync-content:mysql-dump | ssh -p %d %s@%s ", $server->getPort(), $server->getUser(), $server->getHost());
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

        $this->synchronize_content('to', $server, $manager, $output);



        $output->writeln('Synchronization was successful.');
        return true;

    }

    protected function synchronize_content($direction, Server $server, $manager, $output){

        // Synchronize files

        $pathLocal = '.';
        $pathRemote = $server->getUser() . '@' . $server->getHost() . ':' . $server->getDir();
        $port = $server->getPort();

        $options = $manager->getOptions();
        if(isset($options['content'])){

            $output->writeln('Synchronizing content...');
            $results = array();
            foreach ($options['content'] as $content_path) {
                $output->writeln('Synchronizing path: ' . $content_path);

                $local_content_path = "$pathLocal/$content_path";
                $remote_content_path = dirname("$pathRemote/$content_path");

                if(file_exists($local_content_path)){

                    if($direction=='to'){
                        $from = $local_content_path;
                        $to = $remote_content_path;
                    } else { // from
                        $from = $remote_content_path;
                        $to = $local_content_path;
                    }

                    $cmd = "rsync -e 'ssh -p $port' -azC --no-o --no-t --no-p --force --delete --progress " . escapeshellarg($from) . " " . escapeshellarg($to);

                    echo("Executing $cmd\n");
                    system($cmd, $result);
                    if ($result != 0)
                    {
                        $results[] = array('content_path'=>$content_path, 'command'=>$cmd, 'result'=>$result);
                    }

                } else {
                    $results[] = array('content_path'=>$content_path, 'command'=>"file_exists('$local_content_path')", 'result'=>'Local directory does not exist.');
                }

            }
            if(count($results)==0){
                $output->writeln('Content synchronized successfully.');
            } else {
                $errors_str = '';
                foreach($results as $error){
                    $errors_str .= $error['content_path'] . ' -> Command failed :' . $error['command'] . ' || RESULT: ' . $error['result'] . "\n";
                }
                throw new \Exception($errors_str);
            }

        } else {
            $output->writeln('No data path configured to synchronize. If you missed it, it should be done in your config_dev.yml .');
        }

        // END - files

    }
}

