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
            "① 主席" => [
                "1号 董祺悦 庄皓然",
                "2号 何天烁 郑涵泳",
                "3号 施乐天 徐一苇",
                "4号 杨一钦 朱皓泽",
                "此项弃权"
            ],
            "② 文艺宣传部长" => [
                "11号 李雨瑄 臧小凡",
                "12号 殷子扬 赵千寻",
                "此项弃权"
            ],
            "③ 体育部长" => [
                "20号 孙雪涵 郭天成",
                "21号 江雨桥 谭光耀",
                "此项弃权"
            ],
            "④ 学习部长" => [
                "5号 李灵均 刘璟萱",
                "6号 宋宛芸 王吉豪",
                "此项弃权"
            ],
            "⑤ 生活部长" => [
                "18号 曹恒祥 刘思源",
                "19号 李茹 王钰灵",
                "此项弃权"
            ],
            "⑥ 纪检部长" => [
                "16号 冯弋丁 顾起歌",
                "17号 彭百禾 秦梓洋",
                "此项弃权"
            ],

        ]);
        $this->clearDoctrineCache($input, $output);
        $io->success("You have created a 2020 vote (id = $id)!");
    }
}
