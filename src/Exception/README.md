This namespace contains all the exceptions used by the Kitab
project. There is nothing particularily crazy: They are classical
[Hoa exceptions](https://central.hoa-project.net/Resource/Library/Exception).

The `Kitab\Exception\Exception` class represents the root of all
application exceptions.

# Examples

```php,ignore
throw new Kitab\Exception\Exception('Foobar', 42);
```
