<?php


namespace BFOS\SyncContentBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MysqlDumpCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bfos:sync-content:mysql-dump')
            ->setDescription('Outputs a MySQL SQL dump')
        ->setHelp("Example: php app/console bfos:sync-content:mysql-dump ")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $cmd = "mysqldump --skip-opt --add-drop-table --create-options ";
        $cmd .= "--disable-keys --extended-insert --set-charset ";

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

        $cmd .= '-u ' . escapeshellarg($username) . $password . ' -h ' . escapeshellarg($host) . ' ' . escapeshellarg($dbname);





        // Right to stdout for the convenience of the remote ssh connection from
        // sync-content
        system($cmd, $result);

        if ($result != 0)
        {
            throw new sfException("mysqldump failed");
        }


    }

}