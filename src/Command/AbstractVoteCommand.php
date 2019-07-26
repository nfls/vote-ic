<?php
/**
 * Created by PhpStorm.
 * User: huqin
 * Date: 2019/7/24
 * Time: 12:44
 */

namespace App\Command;


use App\Service\VoteManagerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;

class AbstractVoteCommand extends Command
{
    protected static $defaultName = 'app:abstract:vote';
    protected $voteManagerService;

    public function __construct($name = null, VoteManagerService $voteManagerService)
    {
        parent::__construct($name);
        $this->voteManagerService = $voteManagerService;
    }

    protected function clearDoctrineCache($input, $output) {
        $command = $this->getApplication()->find('doctrine:cache:clear-result');
        $command->run(new ArrayInput([]), $output);
        $command = $this->getApplication()->find('doctrine:cache:clear-metadata');
        $command->run(new ArrayInput([]), $output);
        $command = $this->getApplication()->find('doctrine:cache:clear-query');
        $command->run(new ArrayInput([]), $output);
    }
}