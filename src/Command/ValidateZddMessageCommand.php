<?php

namespace Yousign\ZddMessageBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yousign\ZddMessageBundle\Assert\ZddMessageAssert;
use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;
use Yousign\ZddMessageBundle\Factory\ZddMessageFactory;
use Yousign\ZddMessageBundle\Filesystem\ZddMessageFilesystem;

#[AsCommand(name: 'yousign:zdd-message:validate', description: 'Validate the serialized version of managed messages with the current version.')]
final class ValidateZddMessageCommand extends Command
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

        $errorCount = 0;
        foreach ($this->zddMessageConfig->getMessageToAssert() as $messageFqcn) {
            if (false === $this->zddMessageFilesystem->exists($messageFqcn)) {
                // It happens on newly added message, the trade-off here is to validate itself on current version
                $zddMessage = $this->zddMessageFactory->create($messageFqcn);
                $this->zddMessageFilesystem->write($zddMessage);
            }

            $messageToAssert = $this->zddMessageFilesystem->read($messageFqcn);

            try {
                ZddMessageAssert::assert($messageFqcn, $messageToAssert->serializedMessage(), $messageToAssert->notNullableProperties());

                $io->success(\sprintf('Message "%s" is ZDD compliant ✅', $messageFqcn));
            } catch (\Throwable $e) {
                $io->error(\sprintf('Message "%s" is not ZDD compliant ❌. The error is "%s"', $messageFqcn, $e->getMessage()));
                ++$errorCount;
            }
        }

        if (0 !== $errorCount) {
            $io->note(\sprintf('%d error(s) triggered.', $errorCount));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
