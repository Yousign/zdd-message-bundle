<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Utils;

use Yousign\ZddMessageBundle\ZddMessage;

final class ZddMessageFilesystem
{
    public function __construct(private readonly string $zddPath)
    {
    }

    public function write(ZddMessage $zddMessage): void
    {
        $basePath = $this->getBasePath($zddMessage->messageFqcn());
        if (false === file_exists($basePath)) {
            if (!mkdir($basePath, recursive: true) && !is_dir($basePath)) {
                throw new \RuntimeException(\sprintf('Unable to create directory "%s"', $basePath));
            }
        }

        $serializedMessagePath = $this->getPathToSerializedMessage($zddMessage->messageFqcn());
        $byteWrittenInTxt = \file_put_contents($serializedMessagePath, $zddMessage->serializedMessage());
        if (false === $byteWrittenInTxt || 0 === $byteWrittenInTxt) {
            throw new \RuntimeException(\sprintf('Unable to write file "%s"', $serializedMessagePath));
        }

        $notNullablePropertiesPath = $this->getPathToNotNullableProperties($zddMessage->messageFqcn());
        $byteWrittenInJson = \file_put_contents($notNullablePropertiesPath, \json_encode($zddMessage->notNullableProperties()));
        if (false === $byteWrittenInJson || 0 === $byteWrittenInJson) {
            throw new \RuntimeException(\sprintf('Unable to write file "%s"', $notNullablePropertiesPath));
        }
    }

    public function read(string $messageFqcn): ZddMessage
    {
        $serializedMessagePath = $this->getPathToSerializedMessage($messageFqcn);
        if (false === $serializedMessage = \file_get_contents($serializedMessagePath)) {
            throw new \RuntimeException(\sprintf('Unable to read file "%s"', $serializedMessagePath));
        }

        $notNullablePropertiesPath = $this->getPathToNotNullableProperties($messageFqcn);
        if (false === $notNullableProperties = \file_get_contents($notNullablePropertiesPath)) {
            throw new \RuntimeException(\sprintf('Unable to read file "%s"', $notNullablePropertiesPath));
        }
        $notNullableProperties = \json_decode($notNullableProperties, true);

        /* @phpstan-ignore-next-line as $notNullableProperties comes from system */
        return new ZddMessage($messageFqcn, $serializedMessage, $notNullableProperties);
    }

    public function exists(string $messageFqcn): bool
    {
        $serializedMessagePath = $this->getPathToSerializedMessage($messageFqcn);

        return file_exists($serializedMessagePath);
    }

    /**
     * @return array<int, string>
     */
    private function getDirectoryAndShortname(string $classFqcn): array
    {
        $path = explode('\\', $classFqcn);
        $directory = $path[\count($path) - 2];
        $shortName = end($path);

        return [$directory, $shortName];
    }

    private function getPathToSerializedMessage(string $messageFqcn): string
    {
        [$directory, $shortName] = $this->getDirectoryAndShortname($messageFqcn);

        return $this->zddPath.'/'.$directory.'/'.$shortName.'.txt';
    }

    private function getPathToNotNullableProperties(string $messageFqcn): string
    {
        [$directory, $shortName] = $this->getDirectoryAndShortname($messageFqcn);

        return $this->zddPath.'/'.$directory.'/'.$shortName.'.not_nullable_properties.json';
    }

    private function getBasePath(string $messageFqcn): string
    {
        [$directory] = $this->getDirectoryAndShortname($messageFqcn);

        return $this->zddPath.'/'.$directory;
    }
}
