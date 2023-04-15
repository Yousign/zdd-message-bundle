<?php

namespace Yousign\ZddMessageBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yousign\ZddMessageBundle\Assert\ZddMessageAssert;
use Yousign\ZddMessageBundle\Utils\ZddMessageFactory;
use Yousign\ZddMessageBundle\Utils\ZddMessageFilesystem;
use Yousign\ZddMessageBundle\ZddMessageConfigInterface;

#[AsCommand(name: 'yousign:zdd-message', description: 'Serialize and validate ZDD messages.')]
class ZddMessageCommand extends Command
{
    private ZddMessageFactory $zddMessageFactory;
    private ZddMessageFilesystem $zddMessageFilesystem;

    public function __construct(private string $zddMessagePath, private ZddMessageConfigInterface $zddMessageConfig)
    {
        parent::__construct();

        $this->zddMessageFactory = new ZddMessageFactory($zddMessageConfig);
        $this->zddMessageFilesystem = new ZddMessageFilesystem($this->zddMessagePath);
    }

    protected function configure(): void
    {
        $this->addArgument('action', InputArgument::REQUIRED, 'Which action do you want to do ? Available actions: "serialize", "validate".');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $action = $input->getArgument('action');

        switch ($action) {
            case 'serialize':
                return $this->serializeAction($io);
            case 'validate':
                return $this->validateAction($io);
            default:
                $io->error('Please provide a valid action. Available actions: "serialize", "validate".');

                return Command::INVALID;
        }
    }

    private function serializeAction(SymfonyStyle $io): int
    {
        foreach ($this->zddMessageConfig->getMessageToAssert() as $messageFqcn) {
            $zddMessage = $this->zddMessageFactory->create($messageFqcn);

            $this->zddMessageFilesystem->write($zddMessage);

            $io->success(\sprintf('Message "%s" written in directory "%s"', $messageFqcn, $this->zddMessagePath));
        }

        return Command::SUCCESS;
    }

    private function validateAction(SymfonyStyle $io): int
    {
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
