<?php
/**
 * First Image plugin for Craft CMS 5.x
 *
 * @link      https://uxi360.com
 */

namespace uxi360\firstimage\helpers;

/**
 * Small, dependency-free scanner that finds the first <img> in an HTML fragment.
 *
 * It is not a general purpose HTML parser: it only understands enough of HTML
 * (comments, raw-text elements, quoted / unquoted attributes) to reliably pull
 * the source of the first image out of rich text field content.
 *
 * @author    UXI360 Team
 * @since     3.0.0
 */
final class ImageFinder
{
    /**
     * Attributes checked for the image source, in order of preference.
     */
    private const SOURCE_ATTRIBUTES = ['src', 'data-src'];

    /**
     * Returns the source of the first <img> that has one, or null.
     *
     * The value is returned entity-decoded (`&amp;` becomes `&`).
     */
    public static function firstSrc(string $html): ?string
    {
        if ($html === '' || stripos($html, '<img') === false) {
            return null;
        }

        // Remove comments and elements whose content is not markup.
        $clean = preg_replace(
            '~<!--.*?-->|<(script|style|textarea)\b[^>]*>.*?</\1\s*>~is',
            '',
            $html
        );
        if ($clean !== null) {
            $html = $clean;
        }

        // Quote-aware match, so a ">" inside an attribute value doesn't end the tag.
        $found = preg_match_all(
            '~<img(?=[\s/>])((?:[^>"\']++|"[^"]*+"|\'[^\']*+\')*+)>~i',
            $html,
            $matches
        );

        // Fall back to a simple match for broken markup (e.g. an unclosed quote).
        if (!$found) {
            $found = preg_match_all('~<img(?=[\s/>])([^>]*)>~i', $html, $matches);
        }

        if (!$found) {
            return null;
        }

        foreach ($matches[1] as $attributeString) {
            $attributes = self::parseAttributes($attributeString);

            foreach (self::SOURCE_ATTRIBUTES as $name) {
                if (isset($attributes[$name]) && $attributes[$name] !== '') {
                    return $attributes[$name];
                }
            }
        }

        return null;
    }

    /**
     * Parses a tag's attribute string into a lowercase-name => decoded-value map.
     *
     * @return array<string, string>
     */
    private static function parseAttributes(string $attributeString): array
    {
        $attributes = [];

        $found = preg_match_all(
            '~([^\s"\'=<>/]+)(?:\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'<>`]+)))?~',
            $attributeString,
            $matches,
            PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL
        );

        if (!$found) {
            return $attributes;
        }

        foreach ($matches as $match) {
            $name = strtolower($match[1]);

            // As in browsers, the first occurrence of an attribute wins.
            if (array_key_exists($name, $attributes)) {
                continue;
            }

            $value = $match[2] ?? $match[3] ?? $match[4] ?? '';
            $attributes[$name] = trim(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        return $attributes;
    }
}
