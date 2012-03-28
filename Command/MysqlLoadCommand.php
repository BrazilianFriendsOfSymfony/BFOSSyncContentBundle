<?php
/**
 * Created by JetBrains PhpStorm.
 * User: paulo
 * Date: 1/1/12
 * Time: 5:30 PM
 * To change this template use File | Settings | File Templates.
 */

namespace BFOS\SyncContentBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MysqlLoadCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bfos:sync-content:mysql-load')
            ->setDescription('Loads a MySQL SQL dump')
        ->setHelp("Example: php app/console bfos:sync-content:mysql-load")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {



        /**
         * @var \Doctrine\DBAL\Connection $con
         */
        $con = $this->getContainer()->get('database_connection');

        $username = $con->getUsername();
        $password = $con->getPassword();
        $host = $con->getHost();
        $dbname = $con->getDatabase();
        if($password!=null || !empty($password))
            $password = ' -p' . escapeshellarg($password);

        $params = '-u ' . escapeshellarg($username) . $password . ' -h ' . escapeshellarg($host) . ' ' . escapeshellarg($dbname);



        // Accept SQL right from stdin for the convenience of the remote ssh connection from
        // sync-content
        passthru("mysql $params", $result);

        if ($result != 0)
        {
            throw new \Exception("mysql load failed");
        }


    }

}