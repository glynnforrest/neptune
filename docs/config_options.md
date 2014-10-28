# Configuration options

Here is a summary of all available configuration options for the
framework. This includes global options, placed in
`config/neptune.php`, and module options, placed in
`/path/to/module/config.php`.

Remember that global options can be overridden per-environment with
`config/env/<environment>.php`, and module options with
`config/modules/<module>.php`.

## assets

### Global

#### assets.url

Default: `assets/`

The base url all assets are served under.

#### assets.concat_groups

Default: `false`

The `assets:build` command has the option to concatenate asset groups
into a single file. Set this option to `true` to link to these
concatenated files instead of individual assets.

#### assets.cache_bust

Default `false`

Set to `true` to enable a simple cache-busting query string to be
appended to asset links.

### Module

#### assets.css

#### assets.js

#### assets.install_cmd

#### assets.build_cmd
