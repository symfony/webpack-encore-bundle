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

If you want more control, you can use the `encore_entry_js_files()` and
`encore_entry_css_files()` methods to get the list of files needed, then
loop and create the `script` and `link` tags manually.

## Rendering Multiple Templates (e.g. Emails or PDFs)

When you render your script or link tags, the bundle is smart enough
not to repeat the same JavaScript or CSS file within the same request.
This prevents you from having duplicate `<link>` or `<script>` tags
if you render multiple entries that rely on the same file.

But if you're purposely rendering multiple templates in the same
request - e.g. rendering a template for a PDF or to send an email -
then this can cause problems: the later templates won't include any
`<link>` or `<script>` tags that were rendered in an earlier template.

The easiest solution is to render the raw CSS and JavaScript using
a special function that *always* returns the full source, even for files
that were already rendered.

This works especially well in emails thanks to the
[inline_css](https://github.com/twigphp/cssinliner-extra) filter:

```twig
{% apply inline_css(encore_entry_css_source('my_entry')) %}
    <div>
        Hi! The CSS from my_entry will be converted into
        inline styles on any HTML elements inside.
    </div>
{% endapply %}
```

Or you can just render the source directly.

```twig
<style>
    {{ encore_entry_css_source('my_entry')|raw }}
</style>

<script>
    {{ encore_entry_js_source('my_entry')|raw }}
</script>
```

If you can't use these `encore_entry_*_source` functions, you can instead
manually disable and enable "file tracking":

```twig
{# some template that renders a PDF or an email #}

{% do encore_disable_file_tracking() %}
    {{ encore_entry_link_tags('entry1') }}
    {{ encore_entry_script_tags('entry1') }}
{% do encore_enable_file_tracking() %}
```

With this, *all* JS and CSS files for `entry1` will be rendered and
this won't affect any other Twig templates rendered in the request.

## Resetting the Entrypoint

If using `encore_disable_file_tracking()` won't work for you for some
reason, you can also "reset" EncoreBundle's internal cache so that the
bundle re-renders CSS or JS files that it previously rendered. For
example, in a controller:

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
