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

class SampleCommand extends AbstractVoteCommand
{
    protected static $defaultName = 'app:sample';

    protected function configure()
    {
        $this->setDescription('Create an example vote.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $id = $this->voteManagerService->create("2016-2017届剑桥IB国际部学生会竞选（样例）", [
            "① 主席" => [
                "1号 曹恺珺 屠晓芃",
                "2号 高子桉 江慧卓玛",
                "3号 顾平德 骆逸秋"
            ],
            "② 学习部长" => [
                "4号 杭可恬 虞哲雯",
                "5号 何天辰 王轶君",
                "6号 侯千子 徐芷筠"
            ],
            "③ 外联部长" => [
                "7号 曹杨 徐天然",
                "8号 邓心怡 于子千",
                "9号 姚天笑 王云舒"
            ],
            "④ 宣传部长" => [
                "10号 杜从周 经至铭",
                "11号 丁雪凌 戴川滟"
            ],
            "⑤ 文艺部长" => [
                "22号 葛亦婷 康依寻",
                "12号 胡恒越 朱梓源",
                "13号 刘玉儿 金岫"
            ],
            "⑥ 纪检部长" => [
                "14号 方怿 陈家立",
                "15号 梁予馨 王睿卿",
                "16号 刘杨 沈周"
            ],
            "⑦ 生活部长" => [
                "17号 卞金暄 许劭予",
                "18号 顾泊洋 鲁文珂",
                "19号 周子妍 朱花语"
            ],
            "⑧ 体育部长" => [
                "20号 高成 卢苏瀚",
                "21号 姜德一 张正端"
            ]
        ]);

        $io->success("You have created a sample vote (id = $id)!");
    }
}
