# Configuration

## Changing how configuration keys are merged

When loading multiple files, it can be useful to change how
configuration values are merged. The `_options` setting is a special
list of configuration keys that details how the key is merged.

### Avoid merging arrays

A key can be replaced entirely instead of being merged by using the
`no_merge` option.

```yml
_options:
  my_module.foo: no_merge

my_module:
  foo: ['foo', 'bar', 'baz']
```

Now when another configuration containing the `my_module.foo` key is
merged, it will be replaced entirely.

```yml
my_module:
  foo: ['something', 'else']
```

This results in `['something', 'else']` instead of
`['something', 'else', 'baz']`.
