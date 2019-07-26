<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AssociateCommand extends AbstractVoteCommand
{
    protected static $defaultName = 'app:associate';

    protected function configure()
    {
        $this
            ->setDescription('Associate a user with an option.')
            ->addArgument("action", InputArgument::REQUIRED, "Add or delete")
            ->addArgument('username', InputArgument::REQUIRED, 'The name or id of the user')
            ->addArgument('choice', InputArgument::REQUIRED, 'The id of the choice')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $add = $input->getArgument('action') == "add";
        $username = $input->getArgument('username');
        $choice = $input->getArgument("choice");

        $this->voteManagerService->associate($username, $choice, $add);

        $this->clearDoctrineCache($input, $output);

        $io->success("User $username is ".($add?"now":"no longer") . " associated with choice $choice");
    }
}
