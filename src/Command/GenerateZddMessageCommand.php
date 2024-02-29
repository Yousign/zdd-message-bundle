<?php

namespace Yousign\ZddMessageBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;
use Yousign\ZddMessageBundle\Factory\ZddMessageFactory;
use Yousign\ZddMessageBundle\Filesystem\ZddMessageFilesystem;

#[AsCommand(
    name: 'yousign:zdd-message:generate',
    description: 'Generate serialized version of managed messages to validate them afterwards.',
)]
final class GenerateZddMessageCommand extends Command
{
    public function __construct(
        private readonly ZddMessageConfigInterface $config,
        private readonly ZddMessageFactory $messageFactory,
        private readonly ZddMessageFilesystem $filesystem,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $table = $io->createTable();
        $table->setHeaders(['#', 'Message']);

        $row = 1;
        foreach ($this->config->getMessageToAssert() as $key => $instance) {
            $message = $this->messageFactory->create($key, $instance);
            $this->filesystem->write($message);

            $table->addRow([$row++, $key]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
