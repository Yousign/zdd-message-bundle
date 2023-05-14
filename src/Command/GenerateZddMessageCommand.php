<?php

namespace Yousign\ZddMessageBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yousign\ZddMessageBundle\Utils\ZddMessageFactory;
use Yousign\ZddMessageBundle\Utils\ZddMessageFilesystem;
use Yousign\ZddMessageBundle\ZddMessageConfigInterface;

#[AsCommand(name: 'yousign:zdd-message:generate', description: 'Generate serialized version of managed messages to validate them afterwards.')]
class GenerateZddMessageCommand extends Command
{
    private ZddMessageFactory $zddMessageFactory;
    private ZddMessageFilesystem $zddMessageFilesystem;

    public function __construct(private readonly string $zddMessagePath, private readonly ZddMessageConfigInterface $zddMessageConfig)
    {
        parent::__construct();

        $this->zddMessageFactory = new ZddMessageFactory($zddMessageConfig);
        $this->zddMessageFilesystem = new ZddMessageFilesystem($this->zddMessagePath);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->zddMessageConfig->getMessageToAssert() as $messageFqcn) {
            $zddMessage = $this->zddMessageFactory->create($messageFqcn);

            $this->zddMessageFilesystem->write($zddMessage);

            $io->success(\sprintf('Message "%s" written in directory "%s"', $messageFqcn, $this->zddMessagePath));
        }

        return Command::SUCCESS;
    }
}
