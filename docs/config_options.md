# Configuration options

Here is a summary of all available configuration options for the
framework.

# Neptune

## Assets

### Global

#### neptune.assets.url

Default: `assets/`

The base url all assets are served under.

#### neptune.assets.concat_groups

Default: `false`

The `assets:build` command has the option to concatenate asset groups
into a single file. Set this option to `true` to link to these
concatenated files instead of individual assets.

#### neptune.assets.cache_bust

Default `false`

Set to `true` to enable a simple cache-busting query string to be
appended to asset links.

#### <module>.assets.css

#### <module>.assets.js

#### <module>.assets.install_cmd

#### <module>.assets.build_cmd
