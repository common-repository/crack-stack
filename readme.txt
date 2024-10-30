=== Crack Stack ===
Author: Egor Stremousov
Contact: egor.stremousov@gmail.com
Tags: static files, connection limits, optimizing

The plugin increases the page loading speed by changing the URL address of external elements on the page (scripts, styles, images, etc.) and by increasing the number of simultaneous browser connections.


== Description ==

Some modern browsers (such as Mozilla Firefox and Internet Explorer) when the page loads restrict the number of simultaneous connections to a single host. This leads to the creation of a queue of connections and slows speed of download site content. And the more you have external elements on the page, the longer the queue and more time opening the page.

This plugin allows you to automatically reallocate external resources at various pseudo-domains. This removes the limitation on the number of connections and increases the speed of loading pages.

In some cases this can speed up the download images, CSS-styles and JS-scripts in 4 times!

Warning! Your server should correctly convert the queries to non-existent domains, discarding the prefix added by the plugin. Please, before using the plugin configure your server.


== Installation ==

1. Download the plugin (zip file).
2. Upload and activate the plugin through the "Plugins" menu in the WordPress admin.


== Changelog ==

= 1.0 =
* Initial release.