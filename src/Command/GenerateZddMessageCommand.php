<?php

namespace Youtrust\ZddMessageBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Youtrust\ZddMessageBundle\Config\ZddMessageConfigInterface;
use Youtrust\ZddMessageBundle\Factory\ZddMessageFactory;
use Youtrust\ZddMessageBundle\Filesystem\ZddMessageFilesystem;
use Youtrust\ZddMessageBundle\Serializer\SerializerInterface;

#[AsCommand(name: 'youtrust:zdd-message:generate', aliases: ['yousign:zdd-message:generate'], description: 'Generate serialized version of managed messages to validate them afterwards.')]
final class GenerateZddMessageCommand extends Command
{
    private ZddMessageFactory $zddMessageFactory;
    private ZddMessageFilesystem $zddMessageFilesystem;

    public function __construct(private readonly string $zddMessagePath, private readonly ZddMessageConfigInterface $zddMessageConfig, SerializerInterface $serializer)
    {
        parent::__construct();

        $this->zddMessageFactory = new ZddMessageFactory($zddMessageConfig, $serializer);
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
