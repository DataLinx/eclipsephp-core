# About the package

This is the core package for Eclipse, a PHP app built with Filament and the TALL stack.

It's an opinionated package, containing all the core resources and other code, including our selection of Filament
plugins as dependencies. It's included in all our Eclipse projects, and it's purpose is to have as much code as possible,
thus keeping the skeleton/app package as slim as possible to make maintenance of apps easy.

To test the plugin, the contained workbench app is used. This is made possible by Lando, which is already configured and
ready to use by simply running `lando start` in the package root.

Also, read and take into account the [README.md](README.md) file for project-specific documentation.
