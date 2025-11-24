# Zero Downtime Deployment Message Bundle ✉️ ✅

A Symfony Bundle to use when you want to assert that messages used with Message brokers such like RabbitMQ are compliant with the Zero Downtime Deployment.

## Getting started
### Installation
First, install the bundle with composer:
```
$ composer require yousign/zdd-message-bundle
```

Then, verify that the bundle has been registered in `config/bundles.php`:
```php
Yousign\ZddMessageBundle\ZddMessageBundle::class => ['all' => true],
```

### Configuration
Create a class to configure the messages to assert and how to create them:

```php
<?php

namespace App\Message;

use Yousign\ZddMessageBundle\Config\ZddMessageConfigInterface;

class MessageConfig implements ZddMessageConfigInterface
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
     * If you need full control over how a specific message instance is created,
     * use this method to return a fully instantiated message object.
     * This is useful when the default instantiation (using reflection and property injection)
     * is not sufficient or when your message requires specific constructor logic.
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
- `Yousign\ZddMessageBundle\Serializer\ZddMessageMessengerSerializer` (default, already configured for messenger serialization in messenger.yaml)
- Define your own serializer
  - Create a service that implement `Yousign\ZddMessageBundle\Serializer\SerializerInterface`
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
$ bin/console yousign:zdd-message:generate # Generate serialized messages in files.
$ bin/console yousign:zdd-message:validate # Assert that the messages are compliant by deserializing them from files and call the properties.
$ bin/console yousign:zdd-message:debug # Output all tracked messages.
```

💡 You should run `bin/console yousign:zdd-message:generate` with the production version code and `bin/console yousign:zdd-message:validate` with the version code you want to merge.

#### Example from the version you want to merge:
```bash
$ git checkout [production_version]
$ bin/console yousign:zdd-message:generate
$ git checkout - # Go back to the version you want to merge
$ bin/console yousign:zdd-message:validate
```

💡 Use verbose mode to see error details

```
$ bin/console yousign:zdd-message:validate -vv
--- ------------------------------------------------------------------- ---------------- 
#   Message                                                             ZDD Compliant?  
--- ------------------------------------------------------------------- ---------------- 
1   Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage   No ❌           
--- ------------------------------------------------------------------- ---------------- 

! [NOTE] 1 error(s) triggered.                                                                                         

------------------------------------------------------------------- -------------- 
Message                                                             Error         
------------------------------------------------------------------- -------------- 
Yousign\ZddMessageBundle\Tests\Fixtures\App\Messages\DummyMessage   Syntax error  
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
$ ./link /home/yousign/dev/my-project
```

## Authors
- Smaine Milianni - [ismail1432](https://github.com/ismail1432) - <smaine(dot)milianni@gmail(dot)com>
- Simon Mutricy - [Inkod](https://github.com/Inkod) - <ink0d@pm(dot)me>
