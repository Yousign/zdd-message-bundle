# Zero Downtime Deployment Message Bundle ✉️ ✅

A Symfony Bundle to use when you want to assert that messages used with Message brokers such like RabbitMQ are compliant with the Zero Downtime Deployment.

#### :warning: This bundle is still in development (Wait the 1st tag release to use it).

## Getting started
### Installation
You can easily install Zdd Message bundle by composer
```
$ composer require yousign/zdd-message-bundle
```
Then, bundle should be registered. Just verify that `config\bundles.php` is containing :
```php
Yousign\ZddMessageBundle\ZddMessageBundle::class => ['dev' => true, 'test' => true],
```

### Configuration
Once the bundle is installed, you should create a class to configure the messages to assert and how to create them:

```php
<?php

namespace App\Message;

use Yousign\ZddMessageBundle\Message\MessageConfiguratorInterface;

class MessageConfig implements MessageConfiguratorInterface
{
    /**
     * Return the list of messages to assert.
     */
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
    public function getValue(string $typeHint): mixed
    {
        return match ($typeHint) {
            'App\ValueObject\Email' => new App\ValueObject\Email('dummy@email.fr'),
            'App\Enum\MyEnum' => App\Enum\MyEnum::MY_VALUE,
        };
    }
}
```

When the class is created, you can register it as a service.

```yaml
# config/services.yaml
  App\Message\MessageConfig: ~
```
_(If you don't want to register it as a service, the bundle will do it for you.)_

Then, you should register it in the configuration (`config/packages/yousign.yaml`) :
```yaml
# config/packages/yousign.yaml
  zdd_message:
    serialized_messages_dir: 'var/serialized_messages' # The directory where the serialized messages will be stored (default: '%kernel.logs_dir%')
```

## Usage
The bundle comes with commands to assert that your messages are compliant with the Zero Downtime Deployment:

```bash
$ bin/console yousign:zdd-message serialize # Serialize the messages in files
$ bin/console yousign:zdd-message validate # Assert that the messages are compliant by deserializing them from files and call the properties.
$ bin/console yousign:zdd-message:debug # Output all tracked messages
```

💡 You should run `bin/console yousign:zdd-message serialize` with the production version code and `bin/console yousign:zdd-message validate` with the version code you want to merge.

#### Example from the version you want to merge:
```bash
$ git checkout [production_version]
$ bin/console yousign:zdd-message serialize
$ git checkout - # Go back to the version you want to merge
$ bin/console yousign:zdd-message validate
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

## Authors
- Smaine Milianni - [ismail1432](https://github.com/ismail1432) - <smaine(dot)milianni@gmail(dot)com>
- Simon Mutricy - [Inkod](https://github.com/Inkod) - <ink0d@pm(dot)me>
