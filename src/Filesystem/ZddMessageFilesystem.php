<?php

declare(strict_types=1);

namespace Yousign\ZddMessageBundle\Filesystem;

use Yousign\ZddMessageBundle\Factory\Property;
use Yousign\ZddMessageBundle\Factory\ZddMessage;

/**
 * @internal
 */
final class ZddMessageFilesystem
{
    public function __construct(
        private readonly string $path,
    ) {
    }

    public function write(ZddMessage $message): void
    {
        $basePath = $this->getBasePath($message->name);
        if (false === file_exists($basePath)) {
            if (!mkdir($basePath, recursive: true) && !is_dir($basePath)) {
                throw new \RuntimeException(\sprintf('Unable to create directory "%s"', $basePath));
            }
        }

        $serializedMessagePath = $this->getPathToSerializedMessage($message->name);
        $byteWrittenInTxt = \file_put_contents($serializedMessagePath, $message->serializedMessage);
        if (false === $byteWrittenInTxt || 0 === $byteWrittenInTxt) {
            throw new \RuntimeException(\sprintf('Unable to write file "%s"', $serializedMessagePath));
        }

        $propertiesPath = $this->getPathToProperties($message->name);
        $byteWrittenInJson = \file_put_contents(
            $propertiesPath,
            json_encode($message->properties, JSON_THROW_ON_ERROR),
        );
        if (false === $byteWrittenInJson || 0 === $byteWrittenInJson) {
            throw new \RuntimeException(\sprintf('Unable to write file "%s"', $propertiesPath));
        }
    }

    public function read(string $messageName): ZddMessage
    {
        $serializedMessagePath = $this->getPathToSerializedMessage($messageName);
        if (false === $serializedMessage = \file_get_contents($serializedMessagePath)) {
            throw new \RuntimeException(\sprintf('Unable to read file "%s"', $serializedMessagePath));
        }

        $propertiesPath = $this->getPathToProperties($messageName);
        if (false === $propertiesJson = \file_get_contents($propertiesPath)) {
            throw new \RuntimeException(\sprintf('Unable to read file "%s"', $propertiesPath));
        }

        /** @var Property[] $properties */
        $properties = array_map(
            static fn (array $p) => Property::fromArray($p), // @phpstan-ignore-line
            json_decode($propertiesJson, true) // @phpstan-ignore-line
        );

        return new ZddMessage($messageName, $messageName, $serializedMessage, $properties); // TODO: Check 2nd parameter value
    }

    public function exists(string $messageName): bool
    {
        $serializedMessagePath = $this->getPathToSerializedMessage($messageName);

        return file_exists($serializedMessagePath);
    }

    /**
     * @return array<string>
     */
    private function getDirectoryAndShortname(string $messageName): array
    {
        $path = explode('\\', $messageName);
        $shortName = end($path);
        array_pop($path);
        $directory = implode(DIRECTORY_SEPARATOR, $path);

        return [$directory, $shortName];
    }

    private function getPathToSerializedMessage(string $messageName): string
    {
        [$directory, $shortName] = $this->getDirectoryAndShortname($messageName);

        return $this->path.DIRECTORY_SEPARATOR.$directory.DIRECTORY_SEPARATOR.$shortName.'.txt';
    }

    private function getPathToProperties(string $messageName): string
    {
        [$directory, $shortName] = $this->getDirectoryAndShortname($messageName);

        return $this->path.'/'.$directory.'/'.$shortName.'.properties.json';
    }

    private function getBasePath(string $messageName): string
    {
        [$directory] = $this->getDirectoryAndShortname($messageName);

        return $this->path.DIRECTORY_SEPARATOR.$directory;
    }
}
