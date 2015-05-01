# Configuration

## Changing how duplicate configuration is handled

When loading multiple files, it can be useful to specify how key
conflicts should be handled. The `_options` key is a special list
of configuration keys that details how duplicate values are
handled. Each key can be set to one of three options:

* overwrite
* combine
* merge (default)

### Overwrite

The value is replaced entirely instead of being merged.

```yml
_options:
  my_module.foo: overwrite

my_module:
  foo: ['foo', 'bar', 'baz']
```

Now when another configuration containing the `my_module.foo` key is
loaded, it will be replaced entirely.

```yml
my_module:
  foo: ['something', 'else']
```

This results in `['something', 'else']` instead of
`['something', 'else', 'baz']`.

### Combine

### Merge

### Handling conflicts in _options
