<?php

namespace App\Command;

use App\Entity\Vote;
use App\Service\VoteManagerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Er020Command extends AbstractVoteCommand
{
    protected static $defaultName = 'app:2020';

    protected function configure()
    {
        $this->setDescription('Create the vote for 2020.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $id = $this->voteManagerService->create("2020 - 2021届剑桥IB国际部学生会竞选", [
            "「主席」" => [
                "1号 刘韵希 骆珺一",
                "2号 苏思延 张浩然",
                "此项弃权"
            ],
            "「文艺宣传部长」" => [
                "3号 李星辰 夏易如",
                "4号 苗琪悦 钱嘉怡",
                "5号 王思齐 王珍妮",
                "6号 汪雪茜 张顾文萱",
                "此项弃权"
            ],
            "「体育部长」" => [
                "7号 郭子梵 谭哲",
                "8号 刘雨涵 苏语涵",
                "9号 宋昱道 王若丞",
                "此项弃权"
            ],
            "「学习部长」" => [
                "10号 胡家齐 赵如玉",
                "11号 陆嘉洵 周芝祺",
                "此项弃权"
            ],
            "「生活部长」" => [
                "12号 李昱萌 张怡嘉",
                "此项弃权"
            ],
            "「纪检部长」" => [
                "13号 曹紫若 饶培林",
                "此项弃权"
            ],

        ]);
        $this->clearDoctrineCache($input, $output);
        $io->success("You have created a 2020 vote (id = $id)!");
    }
}
