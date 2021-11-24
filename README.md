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
    # if you customize this, you will also need to change framework.assets.json_manifest_path (it usually lives in assets.yaml)
    output_path: '%kernel.project_dir%/public/build'
    # If multiple builds are defined (as shown below), you can disable the default build:
    # output_path: false

    # Set attributes that will be rendered on all script and link tags
    script_attributes:
        defer: true
        # referrerpolicy: origin
    # link_attributes:
    #     referrerpolicy: origin

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

If you're not using Flex, [enable the bundle manually](https://symfony.com/doc/current/bundles.html)
and copy the config file from above into your project.

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

    {# or render a custom attribute #}
    {#
    {{ encore_entry_script_tags('entry1', attributes={
        defer: true
    }) }}
    #}
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

## Custom Attributes on script and link Tags

Custom attributes can be added to rendered `script` or `link` in 3
different ways:

1. Via global config (`script_attributes` and `link_attributes`) - see the
   config example above.

1. When rendering in Twig - see the `attributes` option in the docs above.

1. By listening to the `Symfony\WebpackEncoreBundle\Event\RenderAssetTagEvent`
   event. For example:

```php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\WebpackEncoreBundle\Event\RenderAssetTagEvent;

class ScriptNonceSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            RenderAssetTagEvent::class => 'onRenderAssetTag'
        ];
    }

    public function onRenderAssetTag(RenderAssetTagEvent $event)
    {
        if ($event->isScriptTag()) {
            $event->setAttribute('nonce', 'lookup nonce');
        }
    }
}
```

## Stimulus / Symfony UX Helper

### stimulus_controller

This bundle also ships with a special `stimulus_controller()` Twig function
that can be used to render [Stimulus Controllers & Values](https://stimulus.hotwired.dev/reference/values).
See [stimulus-bridge](https://github.com/symfony/stimulus-bridge) for more details.

For example:

```twig
<div {{ stimulus_controller('chart', { 'name': 'Likes', 'data': [1, 2, 3, 4] }) }}>
    Hello
</div>

<!-- would render -->
<div
   data-controller="chart"
   data-chart-name-value="Likes"
   data-chart-data-value="&#x5B;1,2,3,4&#x5D;"
>
   Hello
</div>
```

Any non-scalar values (like `data: [1, 2, 3, 4]`) are JSON-encoded. And all
values are properly escaped (the string `&#x5B;` is an escaped
`[` character, so the attribute is really `[1,2,3,4]`).

If you have multiple controllers on the same element, pass them all as an
associative array in the first argument:

```twig
<div {{ stimulus_controller({
    'chart': { 'name': 'Likes' },
    'other-controller': { },
) }}>
    Hello
</div>
```

### stimulus_action

The `stimulus_action()` Twig function can be used to render [Stimulus Actions](https://stimulus.hotwired.dev/reference/actions).

For example:

```twig
<div {{ stimulus_action('controller', 'method') }}>Hello</div>
<div {{ stimulus_action('controller', 'method', 'click') }}>Hello</div>

<!-- would render -->
<div data-action="controller#method">Hello</div>
<div data-action="click->controller#method">Hello</div>
```

If you have multiple actions and/or methods on the same element, pass them all as an
associative array in the first argument:

```twig
<div {{ stimulus_action({
 'controller': 'method',
 'other-controller': ['method', {'resize@window': 'onWindowResize'}]
}) }}>
    Hello
</div>

<!-- would render -->
<div data-action="controller#method other-controller#method resize@window->other-controller#onWindowResize">
    Hello
</div>
```

### stimulus_target

The `stimulus_target()` Twig function can be used to render [Stimulus Targets](https://stimulus.hotwired.dev/reference/targets).

For example:

```twig
<div {{ stimulus_target('controller', 'a-target') }}>Hello</div>
<div {{ stimulus_target('controller', 'a-target second-target') }}>Hello</div>

<!-- would render -->
<div data-controller-target="a-target">Hello</div>
<div data-controller-target="a-target second-target">Hello</div>
```

If you have multiple targets on the same element, pass them all as an
associative array in the first argument:

```twig
<div {{ stimulus_target({
 'controller': 'a-target',
 'other-controller': 'another-target'
}) }}>
    Hello
</div>

<!-- would render -->
<div data-controller-target="a-target" data-other-controller-target="another-target">
    Hello
</div>
```

Ok, have fun!
