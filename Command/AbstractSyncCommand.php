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

abstract class AbstractSyncCommand extends ContainerAwareCommand
{

    /**
     * @param $remoteenv
     * @return array
     * @throws \Exception
     */
    protected function checkRemoveEnvParam($remoteenv) {
        if (!preg_match('/^(.*)\@(.*)?$/', $remoteenv, $matches))
        {
            throw new \Exception("remoteenv must be of the form environment@site, example: dev@staging or prod@production; the site must be defined in app/config/deployment_sync_content.yml");
        }
        $envRemote = $matches[1];
        $server = $matches[2];
        return array('env'=>$envRemote, 'server'=>$server);
    }


    protected function synchronize_content($direction, Server $server, ServerRegisterInterface $register, $output){

        // Synchronize files

        $pathLocal = '.';
        $pathRemote = $server->getPath();
        $port = $server->getPort();

        $options = $register->getGlobalOptions();
        if(isset($options['sync_content']['content'])){

            $output->writeln('Synchronizing content...');
            $results = array();
            foreach ($options['sync_content']['content'] as $content_path) {
                $output->writeln('Synchronizing path: ' . $content_path);

                $local_content_path = "$pathLocal/$content_path";
                $remote_content_path = "$pathRemote/$content_path";
//                $remote_content_path = is_dir($remote_content_path)?$remote_content_path:dirname($remote_content_path);

                if(file_exists($local_content_path)){

                    if($direction=='to'){
                        $from = $local_content_path;
                        $to = dirname($remote_content_path);
                    } else { // from
                        $from = $remote_content_path;
                        $to = dirname($local_content_path);
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

