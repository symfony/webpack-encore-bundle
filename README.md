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
    
    # if you have multiple builds:
    # builds:
        # pass "frontend" as the 3rg arg to the Twig functions
        # {{ encore_entry_script_tags('entry1', null, 'frontend') }}

        # frontend: '%kernel.project_dir%/public/frontend/build'

    # Cache the entrypoints.json (rebuild Symfony's cache when entrypoints.json changes)
    # Available in version 1.2
    #cache: '%kernel.debug%'
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

To help with this, Encore writes a `entrypoints.json` file that contains
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

If you want more control, you can use the `encore_entry_js_files()` and
`encore_entry_css_files()` methods to get the list of files needed, then
loop and create the `script` and `link` tags manually.

## Rendering Multiple Times in a Request (e.g. to Generate a PDF)

When you render your script or link tags, the bundle is smart enough
not to repeat the same JavaScript or CSS file within the same request.
This prevents you from having duplicate `<link>` or `<script>` tags
if you render multiple entries that both rely on the same file.

In some cases, however, you may want to render the script & link
tags for the same entry multiple times in a request. For example,
if you render multiple Twig templates to create multiple PDF files
during a single request.

In that case, before each render, you'll need to "reset" the internal
cache so that the bundle re-renders CSS or JS files that it previously
rendered. For example, in a controller:

```php
// src/Controller/SomeController.php

use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

class SomeController
{
    public function index(EntrypointLookupInterface $entrypointLookup)
    {
        $entrypointLookup->reset();
        // render a template

        $entrypointLookup->reset();
        // render another template

        // ...
    }
}
```

If you have multiple builds, you can also autowire
`Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface`
and use it to get the `EntrypointLookupInterface` object for any build.
