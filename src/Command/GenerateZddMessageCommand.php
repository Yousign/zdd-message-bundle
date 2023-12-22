<?php

namespace Yousign\ZddMessageBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;
use Yousign\ZddMessageBundle\Factory\ZddMessageFactory;
use Yousign\ZddMessageBundle\Filesystem\ZddMessageFilesystem;

#[AsCommand(name: 'yousign:zdd-message:generate', description: 'Generate serialized version of managed messages to validate them afterwards.')]
final class GenerateZddMessageCommand extends Command
{
    private ZddMessageFactory $zddMessageFactory;
    private ZddMessageFilesystem $zddMessageFilesystem;

    public function __construct(private readonly string $zddMessagePath, private readonly ZddMessageConfigInterface $zddMessageConfig, ?SerializerInterface $messengerSerializer)
    {
        parent::__construct();

        $this->zddMessageFactory = new ZddMessageFactory($zddMessageConfig, $messengerSerializer);
        $this->zddMessageFilesystem = new ZddMessageFilesystem($this->zddMessagePath);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $table = $io->createTable();
        $table->setHeaders(['#', 'Message']);

        foreach ($this->zddMessageConfig->getMessageToAssert() as $key => $messageFqcn) {
            $zddMessage = $this->zddMessageFactory->create($messageFqcn);

            $this->zddMessageFilesystem->write($zddMessage);

            $table->addRow([$key + 1, $messageFqcn]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
