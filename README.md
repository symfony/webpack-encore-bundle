# WebpackEncoreBundle: Symfony integration with Webpack Encore!

This bundle allows you to use the `splitEntryChunks()` feature
from [Webpack Encore](https://symfony.com/doc/current/frontend.html)
by reading an `entrypoints.json` file and helping you render all of
the dynamic `script` and `link` tags needed.

Install the bundle with:

```
composer require symfony/webpack-encore-bundle
```

## Configuration

If you're using Symfony Flex, you're done! The recipe will
pre-configure everything you need in the `config/packages/webpack_encore.yaml`
file:

```yaml
# config/packages/webpack_encore.yaml
webpack_encore:
    # The path where Encore is building the assets - i.e. Encore.setOutputPath()
    output_path: '%kernel.project_dir%/public/build'
    # If multiple builds are defined (as shown below), you can disable the default build:
    # output_path: false
    
    # if using Encore.enableIntegrityHashes() and need the crossorigin attribute (default: false, or use 'anonymous' or 'use-credentials')
    # crossorigin: 'anonymous'

    # preload all rendered script and link tags automatically via the http2 Link header
    # preload: true

    # Throw an exception if the entrypoints.json file is missing or an entry is missing from the data
    # strict_mode: false
    
    # if you have multiple builds:
    # builds:
        # pass "frontend" as the 3rg arg to the Twig functions
        # {{ encore_entry_script_tags('entry1', null, 'frontend') }}

        # frontend: '%kernel.project_dir%/public/frontend/build'

    # Cache the entrypoints.json (rebuild Symfony's cache when entrypoints.json changes)
    # Available in version 1.2
    # Put in config/packages/prod/webpack_encore.yaml
    # cache: true
```

## Usage

The "Split Chunks" functionality in Webpack Encore is enabled by default
with the recipe if you install this bundle using Symfony Flex. Otherwise,
enable it manually:

```diff
// webpack.config.js
// ...
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .setManifestKeyPrefix('build/')

    .addEntry('entry1', './assets/some_file.js')

+   .splitEntryChunks()
// ...
```

When you enable `splitEntryChunks()`, instead of just needing 1 script tag
for `entry1.js` and 1 link tag for `entry1.css`, you may now need *multiple*
script and link tags. This is because Webpack ["splits" your files](https://webpack.js.org/plugins/split-chunks-plugin/)
into smaller pieces for greater optimization.

To help with this, Encore writes an `entrypoints.json` file that contains
all of the files needed for each "entry".

For example, to render all of the `script` and `link` tags for a specific
"entry" (e.g. `entry1`), you can:

```twig
{# any template or base layout where you need to include a JavaScript entry #}

{% block javascripts %}
    {{ parent() }}

    {{ encore_entry_script_tags('entry1') }}
{% endblock %}

{% block stylesheets %}
    {{ parent() }}

    {{ encore_entry_link_tags('entry1') }}
{% endblock %}
```

Assuming that `entry1` required two files to be included - `build/vendor~entry1~entry2.js`
and `build/entry1.js`, then `encore_entry_script_tags()` is equivalent to:

```twig
<script src="{{ asset('build/vendor~entry1~entry2.js') }}"></script>
<script src="{{ asset('build/entry1.js') }}"></script>
```

## Twig Function Reference

* `encore_entry_script_tags('entryName')` - renders all `<script>` tags for
  the entry.
* `encore_entry_link_tags('entryName')` - renders all `<link` tags for the
  entry.
* `encore_entry_js_files('entryName')` - returns an array of all JavaScript
  files needed for the entry.
* `encore_entry_css_files('entryName')` - returns an array of all CSS
  files needed for the entry.
* `encore_entry_js_source('entryName')` - returns the full source from all
  the JavaScript files needed for this entry.
* `encore_entry_css_source('entryName')` - returns the full source from all
  the CSS files needed for this entry.
* `encore_disable_file_tracking()` - tells Twig to stop "file tracking" and
  render *all* CSS/JS files, even if they were previously rendered.
* `encore_enable_file_tracking()` - tells Twig to re-enable "file tracking" and
  render *only* CSS/JS files tht were not previously rendered.

You can also manually "reset" file tracking in PHP by autowiring
the `Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface` service
and calling `$entrypointLookup->reset()`. If you're using multiple builds,
you can autowire `Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface`
and then ask for your the correct `EntrypointLookupInterface`.
