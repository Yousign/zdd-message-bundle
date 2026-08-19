# Zero Downtime Deployment Message Bundle ✉️ ✅

A Symfony Bundle to use when you want to assert that messages used with Message brokers such like RabbitMQ are compliant with the Zero Downtime Deployment.

## Upgrading from `yousign/zdd-message-bundle`

This bundle was published as `yousign/zdd-message-bundle` up to v3.3.0, under the
`Yousign\ZddMessageBundle\` namespace. Following the company rename to Youtrust, v4.0.0 moves to
`youtrust/zdd-message-bundle` and the `Youtrust\ZddMessageBundle\` namespace. There is no functional
change between v3.3.0 and v4.0.0.

```
$ composer remove yousign/zdd-message-bundle
$ composer require youtrust/zdd-message-bundle:^4.0
```

Then replace every `Yousign\ZddMessageBundle\` prefix with `Youtrust\ZddMessageBundle\`. Beyond your PHP
code, three spots live in configuration and are easy to miss:

- the bundle class in `config/bundles.php`
- the serializer service in `messenger.yaml`, if you use `ZddMessageMessengerSerializer`
- your own implementations of `ZddMessageConfigInterface` and `CustomMessageGeneratorInterface`

The console commands are renamed to `youtrust:zdd-message:*`, with **no alias kept for the old names**.
They are typically invoked from CI pipelines rather than from PHP, so unlike the namespace change this one
fails at run time rather than at autoload time — update your pipelines as part of the upgrade.

`yousign/zdd-message-bundle` is abandoned and no longer maintained. It will receive no further release of
any kind, security fixes included.

## Requirements

- PHP 8.4 and above
- Symfony 7.4 or 8

## Getting started
### Installation
First, install the bundle with composer:
```
$ composer require youtrust/zdd-message-bundle
```

Then, verify that the bundle has been registered in `config/bundles.php`:
```php
Youtrust\ZddMessageBundle\ZddMessageBundle::class => ['all' => true],
```

### Configuration
Create a class to configure the messages to assert and how to create them:

```php
<?php

namespace App\Message;

use Youtrust\ZddMessageBundle\Config\CustomMessageGeneratorInterface;
use Youtrust\ZddMessageBundle\Config\ZddMessageConfigInterface;

class MessageConfig implements ZddMessageConfigInterface, CustomMessageGeneratorInterface
{
    /**
     * Return the list of messages to assert.
     */
    #[\Override]
    public function getMessageToAssert(): array
    {
        return [
            App\Message\MyMessage::class,
            App\Message\AnotherMessage::class,
            //...
        ];
    }

    /**
     * If your message contains no scalar value as parameter such like value enums, value object more complex object,
     * you should use this method to return value for each type hint.
     */
    #[\Override]
    public function generateValueForCustomPropertyType(string $type): mixed
    {
        return match ($type) {
            'App\ValueObject\Email' => new App\ValueObject\Email('dummy@email.fr'),
            'App\Enum\MyEnum' => App\Enum\MyEnum::MY_VALUE,
            default => null,
        };
    }

    /**
     * Optional: Implement CustomMessageGeneratorInterface if you need full control
     * over how a specific message instance is created.
     * This is useful when the default instantiation (using reflection and property injection)
     * is not sufficient or when your message requires specific constructor logic.
     *
     * WARNING: The object must be instantiated with minimum requirements (i.e., nullable properties
     * must be set as null) in order to ensure a good ZDD test.
     */
    #[\Override]
    public function generateCustomMessage(string $className): ?object
    {
        return match ($className) {
            App\Message\ComplexMessage::class => new App\Message\ComplexMessage('custom data'),
            default => null,
        };
    }
}
```

Then, register this class as a service.

```yaml
# config/services.yaml
  App\Message\MessageConfig:
```

Finish by updating the configuration with this new service in `config/packages/zdd_message.yaml`:
```yaml
# config/packages/zdd_message.yaml
  zdd_message:
    message_config_service: App\Message\MessageConfig
```

#### Optional configuration

**Use a custom serializer**

Option to use different serializer.
Possible options:
- `Youtrust\ZddMessageBundle\Serializer\ZddMessageMessengerSerializer` (default, already configured for messenger serialization in messenger.yaml)
- Define your own serializer
  - Create a service that implement `Youtrust\ZddMessageBundle\Serializer\SerializerInterface`
  - Use it in the configuration
```yaml
# config/packages/zdd_message.yaml
  zdd_message:
    serializer: '<your-service-id>'
```

**Custom directory for serialized messages**

Option to specify a custom directory where serialized messages will be stored.

```yaml
# config/packages/zdd_message.yaml
zdd_message:
  # ...
  serialized_messages_dir: '%kernel.project_dir%/custom/path' # Default: '%kernel.project_dir%/var/zdd-message'
```

**Detect messages not tracked**

Option to write a log message if an asynchronous message has been sent (using symfony messenger) and is not present in your configuration.

```yaml
# config/packages/zdd_message.yaml
zdd_message:
  # ...
  log_untracked_messages:
    messenger:
      enable: true # false by default
      level: 'error' # warning by default
```

## Usage
The bundle comes with commands to assert that your messages are compliant with the Zero Downtime Deployment:

```bash
$ bin/console youtrust:zdd-message:generate # Generate serialized messages in files.
$ bin/console youtrust:zdd-message:validate # Assert that the messages are compliant by deserializing them from files and call the properties.
$ bin/console youtrust:zdd-message:debug # Output all tracked messages.
```

💡 You should run `bin/console youtrust:zdd-message:generate` with the production version code and `bin/console youtrust:zdd-message:validate` with the version code you want to merge.

#### Example from the version you want to merge:
```bash
$ git checkout [production_version]
$ bin/console youtrust:zdd-message:generate
$ git checkout - # Go back to the version you want to merge
$ bin/console youtrust:zdd-message:validate
```

💡 Use verbose mode to see error details

```
$ bin/console youtrust:zdd-message:validate -vv
--- ------------------------------------------------------------------- ---------------- 
#   Message                                                             ZDD Compliant?  
--- ------------------------------------------------------------------- ---------------- 
1   Youtrust\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage   No ❌           
--- ------------------------------------------------------------------- ---------------- 

! [NOTE] 1 error(s) triggered.                                                                                         

------------------------------------------------------------------- -------------- 
Message                                                             Error         
------------------------------------------------------------------- -------------- 
Youtrust\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage   Syntax error  
------------------------------------------------------------------- --------------
```

## Contributing
Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

After writing your fix/feature, you can run following commands to make sure that everything is still ok.

```bash
# Install dev dependencies
$ composer install

# Running tests and quality tools locally
$ make all
```

If you want to use your local fork to develop in your projects, you can use the link command to replace the vendor installation by your local version.
```bash
$ ./link /home/youtrust/dev/my-project
```

## Authors
- Smaine Milianni - [ismail1432](https://github.com/ismail1432) - <smaine(dot)milianni@gmail(dot)com>
- Simon Mutricy - [Inkod](https://github.com/Inkod) - <ink0d@pm(dot)me>
