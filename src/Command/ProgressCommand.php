<?php

namespace App\Command;

use App\Entity\Vote;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProgressCommand extends AbstractVoteCommand
{
    protected static $defaultName = 'app:progress';

    protected function configure()
    {
        $this
            ->setDescription('Show progress for a vote.')
            ->addArgument('id', InputArgument::OPTIONAL, 'The vote id.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $id = $input->getArgument("id");
        /** @var Vote $vote */
        $progress = $this->voteManagerService->progress($id);

        foreach ($progress as $key => $group) {
            $io->section($key);
            $io->table(["ID", "Name", "Phone", "Vote"], array_map(function($detail){
                return [$detail["id"], $detail["name"], $detail["phone"], !is_null($detail["ticket"])? "yes" : "no"];
            }, $group));
        }
    }
}
