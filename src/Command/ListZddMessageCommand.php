<?php

namespace Yousign\ZddMessageBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;

#[AsCommand(name: 'yousign:zdd-message:debug', description: 'List of managed messages to validate.')]
final class ListZddMessageCommand extends Command
{
    public function __construct(
        private readonly ZddMessageConfigInterface $zddMessageConfig
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $table = $io->createTable();
        $table->setHeaderTitle('List of tracked messages for the zdd');
        $table->setHeaders(['#', 'Message']);

        foreach ($this->zddMessageConfig->getMessageToAssert() as $key => $message) {
            $table->addRow([$key + 1, $message]);
        }

        $table->render();

        return self::SUCCESS;
    }
}
