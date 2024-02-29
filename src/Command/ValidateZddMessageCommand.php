<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yousign\ZddMessageBundle\Assert\ZddMessageAsserter;
use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;
use Yousign\ZddMessageBundle\Factory\ZddMessageFactory;
use Yousign\ZddMessageBundle\Filesystem\ZddMessageFilesystem;

#[AsCommand(name: 'yousign:zdd-message:validate', description: 'Validate the serialized version of managed messages with the current version.')]
final class ValidateZddMessageCommand extends Command
{
    public function __construct(
        private readonly ZddMessageConfigInterface $config,
        private readonly ZddMessageFactory $messageFactory,
        private readonly ZddMessageFilesystem $filesystem,
        private readonly ZddMessageAsserter $messageAsserter,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $table = $io->createTable();
        $table->setHeaders(['#', 'Message', 'ZDD Compliant?']);

        $errorCount = 0;

        $row = 1;
        foreach ($this->config->getMessageToAssert() as $name => $instance) {
            if (false === $this->filesystem->exists($name)) {
                $message = $this->messageFactory->create(
                    $name,
                    $instance,
                );
                $this->filesystem->write($message);
            }

            $messageToAssert = $this->filesystem->read($name);

            try {
                $this->messageAsserter->assert($instance, $messageToAssert);
                $table->addRow([$row++, $name, 'Yes ✅']);
            } catch (\Throwable $e) {
                $table->addRow([$row++, $name, 'No ❌']);
                ++$errorCount;
            }
        }

        $table->render();

        if (0 !== $errorCount) {
            $io->note(\sprintf('%d error(s) triggered.', $errorCount));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
