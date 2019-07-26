<?php

namespace App\Command;

use App\Entity\Vote;
use App\Library\VoteStatus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetCommand extends AbstractVoteCommand
{
    protected static $defaultName = 'app:set';

    protected function configure()
    {
        $this
            ->setDescription('Set status for a vote.')
            ->addArgument('id', InputArgument::REQUIRED, 'The id for the vote.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $id = $input->getArgument('id');

        $status = $io->choice("Please specify the status:", [
            VoteStatus::getDescription(VoteStatus::HIDDEN),
            VoteStatus::getDescription(VoteStatus::PREVIEWING),
            VoteStatus::getDescription(VoteStatus::VOTING),
            VoteStatus::getDescription(VoteStatus::RESULTS_RELEASED)
            ]);

        $this->voteManagerService->set($id, VoteStatus::getValue($status));



        $this->clearDoctrineCache($input, $output);
        $io->success(("You have successfully set $id to " . $status));
        if($status ==  VoteStatus::getDescription(VoteStatus::RESULTS_RELEASED)) {
            $io->note("Please run app:result to calculate the final result.");
        }



    }
}
