# Changelog

## [v1.15.1](https://github.com/symfony/webpack-encore-bundle/releases/tag/v1.15.1)

*July 13th, 2022*

### Bug

- [#189](https://github.com/symfony/webpack-encore-bundle/pull/189) - Moving deprecated code handling for stimulus_ functions into Twig extension - *@weaverryan*
- [#187](https://github.com/symfony/webpack-encore-bundle/pull/187) - Improve Stimulus phpdoc - *@jmsche*
- [#186](https://github.com/symfony/webpack-encore-bundle/pull/186) - Stimulus: move deprecations from DTOs to filters/functions - *@jmsche*

## [v1.15.0](https://github.com/symfony/webpack-encore-bundle/releases/tag/v1.15.0)

*July 6th, 2022*

### Feature

- [#178](https://github.com/symfony/webpack-encore-bundle/pull/178) - Add Stimulus Twig filters, handle action parameters & allow filters/functions to return array - *@jmsche*

## [v1.14.1](https://github.com/symfony/webpack-encore-bundle/releases/tag/v1.14.1)

*May 3rd, 2022*

### Bug

- [#172](https://github.com/symfony/webpack-encore-bundle/pull/172) - Fixing reset assets trigger on sub-requests - *@TarikAmine*
- [#171](https://github.com/symfony/webpack-encore-bundle/pull/171) - Do not JSON encode stringable values - *@jderusse*

## [v1.14.0](https://github.com/symfony/webpack-encore-bundle/releases/tag/v1.14.0)

*February 14th, 2022*

### Feature

- [#147](https://github.com/symfony/webpack-encore-bundle/pull/147) - Add encore_entry_exists() twig functions to check if entrypoint has files - *@acrobat*

### Bug Fix

- [#115](https://github.com/symfony/webpack-encore-bundle/pull/115) - Reset assets on FINISH_REQUEST - *@Warxcell*

## [v1.13.2](https://github.com/symfony/webpack-encore-bundle/releases/tag/v1.13.2)

*December 2nd, 2021*

### Bug Fix

- [#155](https://github.com/symfony/webpack-encore-bundle/pull/155) - Increase version constraint of symfony/service-contracts - *@luca-rath*

## [v1.13.1](https://github.com/symfony/webpack-encore-bundle/releases/tag/v1.13.1)

*November 28th, 2021*

### Bug Fix

- [#153](https://github.com/symfony/webpack-encore-bundle/pull/153) - Skipping null values from rendering - *@sadikoff*

## [v1.13.0](https://github.com/symfony/webpack-encore-bundle/releases/tag/v1.13.0)

*November 19th, 2021*

### Feature

- [#136](https://github.com/symfony/webpack-encore-bundle/pull/136) - Allow Symfony6 - *@Kocal*, *@weaverryan*

### Bug Fix

- [#126](https://github.com/symfony/webpack-encore-bundle/pull/126) - Remove fallback cache on cache warmer - *@deguif*

## [v1.12.0](https://github.com/symfony/maker-bundle/releases/tag/v1.12.0)

*June 18th, 2021*

### Feature

- [#124](https://github.com/symfony/webpack-encore-bundle/pull/124) - feat(twig): implements stimulus_action() and stimulus_target() Twig functions, close #119 - *@Kocal*

### Bug Fix

- [#111](https://github.com/symfony/webpack-encore-bundle/pull/111) - fix: fix EntrypointLookup Exception - *@jeremyFreeAgent*

## [v1.11.2](https://github.com/symfony/webpack-encore-bundle/releases/tag/v1.11.2)

*April 26th, 2021*

### Bug Fix

- [#122](https://github.com/symfony/webpack-encore-bundle/pull/122) - handle request deprecations - *@jrushlow*
- [#121](https://github.com/symfony/webpack-encore-bundle/pull/121) - [stimulus-controller] fix bool attributes from being rendered incorrectly - *@jrushlow*

## [v1.11.1](https://github.com/symfony/webpack-encore-bundle/releases/tag/v1.11.1)

*February 17th, 2021*

### Bug Fix

- [#113](https://github.com/symfony/webpack-encore-bundle/pull/113) - Fixing null and false attributes  - *@weaverryan*
- [#112](https://github.com/symfony/webpack-encore-bundle/pull/112) - Fix the safety of the stimulus_controller function - *@stof*

## [v1.11.0](https://github.com/symfony/webpack-encore-bundle/releases/tag/v1.11.0)

*February 10th, 2021*

### Feature

- [#110](https://github.com/symfony/webpack-encore-bundle/pull/110) - Adding a simpler syntax for single stimulus controller elements - *@weaverryan*

## [v1.10.0](https://github.com/symfony/webpack-encore-bundle/releases/tag/v1.10.0)

*February 10th, 2021*

### Feature

- [#109](https://github.com/symfony/webpack-encore-bundle/pull/109) - Introduce stimulus_controller to ease Stimulus Values API usage - *@tgalopin*
- [#104](https://github.com/symfony/webpack-encore-bundle/pull/104) - Allow custom EntrypointLookupCollection when instantiating the TagRenderer - *@richardhj*

## [v1.9.0](https://github.com/symfony/webpack-encore-bundle/releases/tag/v1.9.0)

*January 15th, 2021*

### Feature

- [#102](https://github.com/symfony/webpack-encore-bundle/pull/102) - Adding support for custom attributes on rendered script and link tags - *@weaverryan*

## [v1.8.0](https://github.com/symfony/webpack-encore-bundle/releases/tag/v1.8.0)

*October 28th, 2020*

### Feature

- [#98](https://github.com/symfony/webpack-encore-bundle/pull/98) - PHP 8.0 compatibility - *@jmsche*
