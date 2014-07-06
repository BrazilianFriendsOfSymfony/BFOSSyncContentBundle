<?php

namespace BFOS\SyncContentBundle\Command;

use BFOS\SyncContentBundle\Server\ServerInterface;
use BFOS\SyncContentBundle\Server\ServerRegisterInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ShowServerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bfos:sync-content:show-server')
            ->setDescription('Shows a server')
            ->addArgument('server', InputArgument::OPTIONAL, 'The server name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager   = $this->getContainer()->get('bfos_sync_content.server_register');
        $server = $input->getArgument('server');

        if (null === $server) {
            $servers = $manager->getServers();
        } else {
            $servers = array($server => $manager->getServer($server));
        }

        /**
         * @var ServerRegisterInterface $register
         */
        $register = $this->getContainer()->get('bfos_sync_content.server_register');

        foreach ($servers as $name => $server) {
            $this->showServer($name, $server, $register, $output);
        }
    }

    protected function showServer($name, ServerInterface $server, ServerRegisterInterface $register, OutputInterface $output)
    {
        $password = '';
        if (null !== $server->getPassword()) {
            $password = str_repeat('*', strlen($server->getPassword()));
        }

        $output->writeln(sprintf('Informations for <info>%s</info> server:', $name));
        $output->writeln(sprintf('    > <comment>host</comment>:     %s', $server->getHost()));
        $output->writeln(sprintf('    > <comment>dir</comment>:      %s', $server->getDir()));
        $output->writeln(sprintf('    > <comment>user</comment>:     %s', $server->getUser()));
        $output->writeln(sprintf('    > <comment>password</comment>: %s', $password));
        $output->writeln(sprintf('    > <comment>port</comment>:     %s', $server->getPort()));

        $options = $register->getMergedOptions($server);
        if (0 !== count($options)) {
            $output->writeln('    > <comment>options</comment>:');
            foreach ($options as $key => $value) {
                $output->writeln(sprintf('        > <comment>%s</comment>: %s', $key, (string)$value));
            }
        }
    }
}

