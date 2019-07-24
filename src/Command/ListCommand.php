<?php

namespace App\Command;

use App\Entity\Vote;
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
        $votes = $this->voteManagerService->list();

        $io->table(["id", "title", "enabled"], array_map(function($vote) {
            /** @var $vote Vote */
            return [$vote->getId()->toString(), $vote->getTitle(), $vote->isEnabled()? "true": "false"];
        }, $votes));

        $io->success('All votes listed.');
    }
}
