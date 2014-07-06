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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use \BFOS\SyncContentBundle\Server\Server;

class DeployCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bfos:deploy')
            ->setDescription('Deploy your app TO another server')
            ->addArgument('server', InputArgument::REQUIRED, 'The server name .')
            ->setHelp(<<<EOF
The <info>bosf:deploy</info> command deploys you app from your computer to a remote server using rsync:

  <info>php app/console bfos:deploy staging</info>
  <info>php app/console bfos:deploy production</info>

The server must be configured in <comment>app/config/deployment_sync_content.yml</comment>:

    servers:
        production:
            host: www.mywebsite.com
            port: 22
            user: julien
            dir: /var/www/sfblog/

To automate the deployment, the task uses rsync over SSH.
You must configure SSH access with a key or configure the password
in <comment>app/config/deployment_sync_content.yml</comment>.
EOF
            )
        ;
    }



    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $servername = $input->getArgument('server');

        /**
         * @var ServerRegisterInterface $register
         */
        $register = $this->getContainer()->get('bfos_sync_content.server_register');

        $server = $register->getServer($servername);

        $this->synchronize_content($server, $register, $output);

        $output->writeln('Synchronization was successful.');
        return true;

    }


    protected function synchronize_content(Server $server, ServerRegisterInterface $register, OutputInterface $output){

        // Synchronize files

        $pathRemote = $server->getPath();
        $port = $server->getPort();

        $options = $register->getMergedOptions($server);

        $output->writeln('Deploying app...');
        $results = array();

        $content_path = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../') . '/';

        // Execute the local commands, before deployment
        if(isset($options['deployment']['pre_local_commands'])){
            foreach($options['deployment']['pre_local_commands'] as $cmd){
                $output->writeln("Executing (local)...: \n - " . $cmd);
                system($cmd);
            }
        }

        // Execute the remote commands, before deployment
        if(isset($options['deployment']['pre_remote_commands'])){
            $this->executeRemoteCommands($options['deployment']['pre_remote_commands'], $server, $output);
        }


        $output->writeln('Synchronizing path: ' . $content_path);

        $local_content_path = $content_path;
//        $remote_content_path = rtrim($pathRemote, '/') . '/';
        $remote_content_path = $pathRemote;

        if (file_exists($local_content_path)) {

            $from = $local_content_path;
            $to = $remote_content_path;

//            $cmd = "rsync -e 'ssh -p $port' -azC --no-o --no-t --no-p --force --delete --progress " ;
            $cmd = "rsync -e 'ssh -p $port' -azCcI --no-t --force --delete --progress " ;
            if(isset($options["deployment"]['rsync_exclude'])){
                $cmd .= " --exclude-from=".$options["deployment"]['rsync_exclude'] . " ";
            }
            $cmd .= escapeshellarg($from) . " " . escapeshellarg($to);

            echo("Executing $cmd\n");
            system($cmd, $result);
            if ($result != 0)
            {
                $results[] = array('content_path'=>$content_path, 'command'=>$cmd, 'result'=>$result);
            }

        } else {
            $results[] = array('content_path'=>$content_path, 'command'=>"file_exists('$local_content_path')", 'result'=>'Local directory does not exist.');
        }

        if(count($results)==0){

            // Execute the remote commands, after deployment
            if(isset($options['deployment']['post_remote_commands'])){
                $this->executeRemoteCommands($options['deployment']['post_remote_commands'], $server, $output);
            }

            // Execute the local commands, after deployment
            if(isset($options['deployment']['post_local_commands'])){
                foreach($options['deployment']['post_local_commands'] as $cmd){
                    $output->writeln("Executing (local)...: \n - " . $cmd);
                    system($cmd);
                }
            }

            $output->writeln('App deployed successfully.');
        } else {
            $errors_str = '';
            foreach($results as $error){
                $errors_str .= $error['content_path'] . ' -> Command failed :' . $error['command'] . ' || RESULT: ' . $error['result'] . "\n";
            }
            throw new \Exception($errors_str);
        }

        // END - files

    }

    protected function executeRemoteCommands($commands, Server $server, OutputInterface $output){

        $cmd_remote = "";
        foreach($commands as $command){
            $cmd_remote .= "; ". $command;
        }

        $cmd = sprintf("ssh -p %d %s@%s ", $server->getPort(), $server->getUser(), $server->getHost());
        $cmd .= escapeshellarg("(cd " . escapeshellarg($server->getDir()) . $cmd_remote . " )");

        $output->writeln('Executing the following commands on remote server...:' . "\n - " . implode("\n - " , $commands));
        $output->writeln($cmd);

        // Right to stdout for the convenience of the remote ssh connection from
        // sync-content
        system($cmd, $result);

        if ($result != 0)
        {
            throw new \Exception("Remote commands failed");
        }
        $output->writeln('Remote commands executed successfully.');
    }

}

