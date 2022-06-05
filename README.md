# First Image plugin for Craft CMS 3.x

A plugin to get first image from Redactor Field.

## Requirements

This plugin requires Craft CMS 3.0.0 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require by/first-image

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for First Image.
## Using First Image

There are two ways of getting first image from a redactor field:

    {{ entry.body | getFirstImage }}

or 

    {% set image = getFirstImage(entry.body) %}
    
This will output the source of the first image found, which then can be used in `<img>` tag.

Brought to you by [Bhashkar Yadav](https://360adaptive.com)
