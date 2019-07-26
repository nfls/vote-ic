<?php

namespace App\Command;

use App\Entity\Choice;
use App\Entity\Section;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ResultCommand extends AbstractVoteCommand
{
    protected static $defaultName = 'app:result';

    protected function configure()
    {
        $this
            ->setDescription('Show results for a vote')
            ->addArgument('id', InputArgument::REQUIRED, 'Vote id')
            ->addArgument("detail", InputArgument::OPTIONAL, "")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $id = $input->getArgument('id');

        $this->voteManagerService->calculate($id);
        $this->clearDoctrineCache($input, $output);
        if($input->getArgument("detail") != null) {
            $result = $this->voteManagerService->result($id, true);
            foreach ($result->toArray() as $section) {
                /** @var Section $section */
                $io->section($section->getName());
                $io->table(["Name", "Count", "Adjust", "Result"], array_map(function($result){
                    /** @var Choice $result */
                    return [$result->getName(), $result->getCount(), $result->getAdjust(), $result->getResult()];
                }, $section->getChoices()->toArray()));
            }

        } else {
            $result = $this->voteManagerService->result($id, false);
            $io->table(["Name", "Win", "Result"], array_map(function($result){
                return [$result["name"], $result["maxChoice"]["name"], $result["maxChoice"]["result"]];
            }, $result));
        }
    }
}
