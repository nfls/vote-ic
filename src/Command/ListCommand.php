<?php

namespace App\Command;

use App\Entity\Vote;
use App\Library\VoteStatus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ListCommand extends AbstractVoteCommand
{
    protected static $defaultName = 'app:list';

    protected function configure()
    {
        $this->setDescription('List all votes.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $votes = $this->voteManagerService->listAll();

        $io->table(["id", "title", "status"], array_map(function($vote) {
            /** @var $vote Vote */
            return [$vote->getId()->toString(), $vote->getTitle(), VoteStatus::getDescription($vote->getStatus())];
        }, $votes));

        $io->success('All votes listed.');
    }
}
