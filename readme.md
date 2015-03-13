## Introduction

Solder Toolbelt is a command line tool developed by [Indemnity83](http://brothersklaus.com/members/Indemnity83/) that aids in packing mods for distribution. It allows you to easily produce the zip file required by Technic Solder for distribution in a single command. It's the tool we use to package mods in our modpacks!

Solder Toolbelt reads data directly from the forge mcmod.info file located in the jar file. This means packaging mod files into distribution zips takes almost no effort on your part.

## Installation &amp; Setup

### Installing PHP & Composer

Solder Toolbelt requires composer and php be installed on your system. Installation of these tools is beyond the scope of this document, but support can be found below

Windows
*   [PHP Install](http://php.net/manual/en/install.windows.php)
*   [Composer Install](https://getcomposer.org/doc/00-intro.md#installation-windows)

OSX
*   [PHP Install](http://php.net/manual/en/install.macosx.php)
*   [Composer Install](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)

Linux
*   [PHP Install](http://php.net/manual/en/install.unix.php)
*   [Composer Install](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)

### Installing Solder Toolbelt

Once PHP and Composer have been installed, you are ready to install the Solder Toolbelt CLI tool using the Composer global command:

`composer global require "indemnity83/solder-toolbelt"`

Make sure to place the ~/.composer/vendor/bin directory in your PATH so the Solder Toolbelt executable is found when you run the bakery command in your terminal.

## Quick Start

To package a mod file (.jar or .zip) into a distribution package, simply use the following command. The mcmod.info will be read from the mod file and used to create a folder, containing a zip that is named by a pre-established convention of <mod>-<mcver>-<modver>.zip

`solder mod pack <path to mod file>`
