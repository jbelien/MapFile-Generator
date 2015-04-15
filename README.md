# MapFile Generator
## MapServer .MAP Generator (Read, Write & Preview)

Still in development ! Use with caution.

**Feel free to test it, criticize it and make any suggestion !** :)

--------------------------------------------------

## Required :

* Web Server ([Apache](http://httpd.apache.org/), [NGINX](http://nginx.org/), ...)
* [PHP](http://php.net/) 5.3+
* [MapServer](http://mapserver.org/)
* [[MapScript](http://www.mapserver.org/mapscript/index.html)]

--------------------------------------------------

## Installation

Just copy these files in a directory accessible via HTTP.

## Configuration

Create a `setting.ini` file in "MapFile Generator" root directory.

These are the parameters available :

* *mapserv* : url path to `mapserv`
* *fontset* : full path to MapServer fontset file
* *symbolset* : full path to MapServer symbolset file
* *font* : default fontname (label, legend, scalebar, ...)
* *directory* : full path to directory containing .map files

Example :

    mapserv   = "/cgi-bin/mapserv"
    fontset   = "/usr/lib/cgi-bin/fonts.txt"
    symbolset = "/usr/lib/cgi-bin/symbols.txt"
    font      = "dejavusans"
    directory = "/var/www/mapserver-data"

## Usage

Go to [Documentation wiki page](https://github.com/jbelien/MapFile-Generator/wiki/Documentation).

## Libraries

If [PHP MapScript](http://www.mapserver.org/mapscript/index.html) is not enabled, this application use internal library to read and write the MapFile.

Documentation of the internal library : <http://jbelien.github.io/MapFile-Generator/library/>
