<?php

namespace App\Command;

use App\Service\VoteManagerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateCommand extends AbstractVoteCommand
{
    protected static $defaultName = 'app:create';


    protected function configure()
    {
        $this
            ->setDescription('Add a new vote.')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the vote')
            ->addArgument('config', InputArgument::REQUIRED, 'The json configuration of the vote.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');
        $config = json_decode($input->getArgument('config'), true);

        $id = $this->voteManagerService->create($name, $config);
        $this->clearDoctrineCache($input, $output);
        $io->success("You have created a new vote (id = $id)!");
    }
}
