# Liip Serializer JMS Adapter

An adapter to make liip/serializer a drop-in replacement for `jms/serializer`.

Version 1 of this adapter is for integrating JMS Serializer 1.*, and version 2
for JMS Serializer 2.*. (Because there are BC breaks in the interfaces of JMS
serializer that prevent this library from supporting JMS 1 and 2 with the same
code.)

This library provides a serializer that attempts to use the Liip Serializer and
falls back to JMS serializer on error. You need to use the 
`Liip\Serializer\Adapter\JMS\AdapterSerializationContext` provided in this
library instead of the regular `JMS\Serializer\SerializationContext`. You can
use the `Serializer\Adapter\JMS\AdapterSerializationContextFactory` when
working with libraries that create the context themselves.

# Usage

```php
use JMS\Serializer\Serializer as JMSSerializer;
use Liip\Serializer\Adapter\JMS\JMSSerializerAdapter;
use Liip\Serializer\Serializer as LiipSerializer;

// see https://github.com/liip/serializer/ for how to set up the Liip Serializer
$liipSerializer = new LiipSerializer(...);
// see https://jmsyst.com/libs/serializer for how to set up JMS Serializer
$jmsSerializer = new JMSSerializer(...);

$serializer = new JMSSerializerAdapter($liipSerializer, $jmsSerializer, new NullLogger());

// $serializer can now be used in place of $jmsSerializer
```

## Using Liip Serializer only for a Subset of Models

The `JMSSerializerAdapter` accepts an additional constructor argument to specify
a list of enabled classes. If that list is specified, only those classes will
be attempted to handle with the Liip Serializer. If you want to use JMS
Serializer for some of your models and the Liip Serializer for others, use this
configuration to avoid overhead and avoid warning log entries from the adapter
that you do not care about.

```php
$enabledClasses = ['App\Model\Product', 'App\Model\Category'];

$serializer = new JMSSerializerAdapter($liipSerializer, $jmsSerializer, new NullLogger(), $enabledClasses);
```
