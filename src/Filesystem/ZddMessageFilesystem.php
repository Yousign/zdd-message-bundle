<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Filesystem;

use Yousign\ZddMessageBundle\Factory\PropertyList;
use Yousign\ZddMessageBundle\Factory\ZddMessage;

/**
 * @internal
 */
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

        $propertiesPath = $this->getPathToProperties($zddMessage->messageFqcn());
        $byteWrittenInJson = \file_put_contents($propertiesPath, $zddMessage->propertyList()->toJson());
        if (false === $byteWrittenInJson || 0 === $byteWrittenInJson) {
            throw new \RuntimeException(\sprintf('Unable to write file "%s"', $propertiesPath));
        }
    }

    public function read(string $messageFqcn): ZddMessage
    {
        $serializedMessagePath = $this->getPathToSerializedMessage($messageFqcn);
        if (false === $serializedMessage = \file_get_contents($serializedMessagePath)) {
            throw new \RuntimeException(\sprintf('Unable to read file "%s"', $serializedMessagePath));
        }

        $propertiesPath = $this->getPathToProperties($messageFqcn);
        if (false === $properties = \file_get_contents($propertiesPath)) {
            throw new \RuntimeException(\sprintf('Unable to read file "%s"', $propertiesPath));
        }

        $propertyList = PropertyList::fromJson($properties);

        return new ZddMessage($messageFqcn, $serializedMessage, $propertyList);
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
        $shortName = end($path);
        array_pop($path);
        $directory = implode('/', $path);

        return [$directory, $shortName];
    }

    private function getPathToSerializedMessage(string $messageFqcn): string
    {
        [$directory, $shortName] = $this->getDirectoryAndShortname($messageFqcn);

        return $this->zddPath.'/'.$directory.'/'.$shortName.'.txt';
    }

    private function getPathToProperties(string $messageFqcn): string
    {
        [$directory, $shortName] = $this->getDirectoryAndShortname($messageFqcn);

        return $this->zddPath.'/'.$directory.'/'.$shortName.'.properties.json';
    }

    private function getBasePath(string $messageFqcn): string
    {
        [$directory] = $this->getDirectoryAndShortname($messageFqcn);

        return $this->zddPath.'/'.$directory;
    }
}
