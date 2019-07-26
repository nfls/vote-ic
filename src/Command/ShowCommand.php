<?php

namespace App\Command;

use App\Entity\Choice;
use App\Entity\Section;
use App\Entity\Vote;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ShowCommand extends AbstractVoteCommand
{
    protected static $defaultName = 'app:show';

    protected function configure()
    {
        $this
            ->setDescription('Show details for a vote.')
            ->addArgument('id', InputArgument::OPTIONAL, 'The vote id.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $id = $input->getArgument("id");


        /** @var Vote $vote */
        $vote = $this->voteManagerService->retrieve($id);

        $io->note($vote->getId());
        $io->note($vote->getTitle());
        $io->note($vote->getContent());

        $io->newLine(1);

        foreach ($vote->getSections()->toArray() as $section) {
            /** @var Section $section */
            $io->section($section->getName());
            $io->table(["ID", "Name", "Associate"], array_map(function($choice){
                /** @var Choice $choice */
                return [$choice->getId(), $choice->getName(), array_reduce($choice->getUsers()->toArray(), function($carry, $user){
                    return $carry . " " . $user->getName();
                }, "")];
            }, $section->getChoices()->toArray()));
        }

    }
}
