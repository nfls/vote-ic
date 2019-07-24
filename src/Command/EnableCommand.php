<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EnableCommand extends AbstractVoteCommand
{
    protected static $defaultName = 'app:enable';

    protected function configure()
    {
        $this
            ->setDescription('Enable or disable a vote.')
            ->addArgument('id', InputArgument::REQUIRED, 'The id for the vote.')
            ->addArgument("status", InputArgument::REQUIRED, 'Status, either enabled or disabled.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $id = $input->getArgument('id');
        $status = $input->getArgument('status') == "enabled";

        $this->voteManagerService->set($id, $status);

        $io->success(("You have successfully set $id to " .($status? "enabled":"disabled")));
        $io->note("Please make sure that no more than one vote is enabled.");
    }
}
