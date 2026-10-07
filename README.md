# First Image plugin for Craft CMS 5.x

Get the first image from a Redactor or CKEditor field - optionally with an image transform applied.

## Requirements

- Craft CMS 5.0.0 or later
- PHP 8.2 or later

For Craft CMS 4, use `by/first-image` 2.x. For Craft CMS 3, use `by/first-image` 1.x.

## Installation

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Tell Composer to load the plugin:

        composer require uxi360/first-image

3. Install the plugin, either from **Settings → Plugins** in the control panel or with:

        php craft plugin/install first-image

## Upgrading to Craft 5 (from `by/first-image`)

As of 3.0.0 the package is named **`uxi360/first-image`** (it used to be `by/first-image`). The plugin handle is still `first-image`, so Craft treats it as the same plugin: there is nothing to uninstall or reinstall, and no template changes are required.

1. Update Craft itself to Craft 5 first, following the [Craft 5 upgrade guide](https://craftcms.com/docs/5.x/upgrade.html).

2. Swap the package in your project:

        composer remove by/first-image --no-update
        composer require uxi360/first-image:^3.0 -W

   Or edit your project's `composer.json` by hand, replacing

        "by/first-image": "2.0.0"

   with

        "uxi360/first-image": "^3.0.0"

   and run `composer update by/first-image uxi360/first-image`.

3. Apply any pending changes and clear the compiled templates:

        php craft up
        php craft clear-caches/compiled-templates

Do **not** uninstall the plugin from the control panel while doing this - only the Composer package changes.

What changes for you:

- The PHP namespace is now `uxi360\firstimage` (was `by\firstimage`). This only matters if you reference the plugin's classes from your own PHP code.
- An empty field now returns `null` instead of raising an error.
- A second argument to `getFirstImage` is now treated as an image transform (it used to be ignored). Check any templates that already pass one.

## Using First Image

`getFirstImage` is available as a Twig filter and as a Twig function. It returns the URL of the first image in the content, or `null` when there isn't one.

    {{ entry.body | getFirstImage }}

or

    {% set image = getFirstImage(entry.body) %}

Typical usage:

    {% set image = entry.body | getFirstImage %}
    {% if image %}
        <img src="{{ image }}" alt="{{ entry.title }}">
    {% endif %}

### Image transforms

Pass a transform the same way you would to `asset.getUrl()`. It works with both the filter and the function:

    {{ entry.body | getFirstImage('thumb') }}
    {{ getFirstImage(entry.body, 'thumb') }}

A named transform, by its handle:

    {{ entry.body | getFirstImage('thumb') }}

A transform definition:

    {{ entry.body | getFirstImage({
        mode: 'crop',
        width: 100,
        height: 100,
        quality: 75,
        position: 'top-center'
    }) }}

Or from a variable, with the function syntax:

    {% set thumb = { mode: 'fit', width: 600, format: 'webp' } %}
    {% set image = getFirstImage(entry.body, thumb) %}

Transforms can only be applied to images that are Craft assets. In these cases the original image URL is returned unchanged:

- the image is not a Craft asset (for example, an image hot-linked from another site);
- the transform handle doesn't exist (a warning is written to the Craft log).

### Supported content

- **Redactor** and **CKEditor** field values (`entry.body`). Pass the field value itself rather than a string built from it, so the plugin can identify the asset behind the image.
- Any other HTML string. With a transform, the plugin tries to match the image URL to an asset through your volumes' base URLs.

If an image has no `src`, its `data-src` attribute is used instead.

Brought to you by [UXI360 InfoTech](https://uxi360.com)
