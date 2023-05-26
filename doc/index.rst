WebpackEncoreBundle: Symfony integration with Webpack Encore!
=============================================================

This bundle allows you to use the ``splitEntryChunks()`` feature
from `Webpack Encore`_ by reading an ``entrypoints.json`` file and
helping you render all of the dynamic ``script`` and ``link`` tags
needed.

Installation
------------

Install the bundle with:

.. code-block:: terminal

    $ composer require symfony/webpack-encore-bundle


Configuration
-------------

If you're using Symfony Flex, you're done! The recipe will
pre-configure everything you need in the ``config/packages/webpack_encore.yaml``
file:

.. code-block:: yaml

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
            # frontend: '%kernel.project_dir%/public/frontend/build'

            # pass the build name" as the 3rd argument to the Twig functions
            # {{ encore_entry_script_tags('entry1', null, 'frontend') }}

        # Cache the entrypoints.json (rebuild Symfony's cache when entrypoints.json changes)
        # Available in version 1.2
        # Put in config/packages/prod/webpack_encore.yaml
        # cache: true

If you're not using Flex, `enable the bundle manually`_
and copy the config file from above into your project.

Usage
-----

The "Split Chunks" functionality in Webpack Encore is enabled by default
with the recipe if you install this bundle using Symfony Flex. Otherwise,
enable it manually:

.. code-block:: diff

    // webpack.config.js
    // ...
        .setOutputPath('public/build/')
        .setPublicPath('/build')
        .setManifestKeyPrefix('build/')
        .addEntry('entry1', './assets/some_file.js')

    +   .splitEntryChunks()
    // ...

When you enable ``splitEntryChunks()``, instead of just needing 1 script tag
for ``entry1.js`` and 1 link tag for ``entry1.css``, you may now need *multiple*
script and link tags. This is because Webpack `"splits" your files`_
into smaller pieces for greater optimization.

To help with this, Encore writes an ``entrypoints.json`` file that contains
all of the files needed for each "entry".

For example, to render all of the ``script`` and ``link`` tags for a specific
"entry" (e.g. ``entry1``), you can:

.. code-block:: twig

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

Assuming that ``entry1`` required two files to be included - ``build/vendor~entry1~entry2.js``
and ``build/entry1.js``, then ``encore_entry_script_tags()`` is equivalent to:

.. code-block:: html+twig

    <script src="{{ asset('build/vendor~entry1~entry2.js') }}"></script>
    <script src="{{ asset('build/entry1.js') }}"></script>

If you want more control, you can use the ``encore_entry_js_files()`` and
``encore_entry_css_files()`` methods to get the list of files needed, then
loop and create the ``script`` and ``link`` tags manually.

Rendering Multiple Times in a Request (e.g. to Generate a PDF)
--------------------------------------------------------------

When you render your script or link tags, the bundle is smart enough
not to repeat the same JavaScript or CSS file within the same request.
This prevents you from having duplicate ``<link>`` or ``<script>`` tags
if you render multiple entries that both rely on the same file.

In some cases, however, you may want to render the script & link
tags for the same entry multiple times in a request. For example,
if you render multiple Twig templates to create multiple PDF files
during a single request.

In that case, before each render, you'll need to "reset" the internal
cache so that the bundle re-renders CSS or JS files that it previously
rendered. For example, in a controller::

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

If you have multiple builds, you can also autowire
``Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface``
and use it to get the ``EntrypointLookupInterface`` object for any build.

Custom Attributes on script and link Tags
-----------------------------------------

Custom attributes can be added to rendered ``script`` or ``link`` in 3
different ways:

#. Via global config (``script_attributes`` and ``link_attributes``) - see the
   config example above.
#. When rendering in Twig - see the ``attributes`` option in the docs above.
#. By listening to the ``Symfony\WebpackEncoreBundle\Event\RenderAssetTagEvent``
   event. For example::

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

Stimulus / Symfony UX Helper
----------------------------

Version 1 of this bundle came with  ``stimulus_controller()``,
``stimulus_action()`` and ``stimulus_target()`` Twig functions. These have been
removed: use `symfony/stimulus-bundle`_ instead.

.. _`Webpack Encore`: https://symfony.com/doc/current/frontend.html
.. _`enable the bundle manually`: https://symfony.com/doc/current/bundles.html
.. _`"splits" your files`: https://webpack.js.org/plugins/split-chunks-plugin/
.. _`symfony/stimulus-bundle`: https://symfony.com/bundles/StimulusBundle/current/index.html
