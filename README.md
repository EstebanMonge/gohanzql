# Gohan ZQL

Gohan ZQL is a web based administration tool for Nagios/Icinga configuration.

It is a **fork of [NagiosQL](https://sourceforge.net/projects/nagiosql/)** (version 3.5.0), the tool created by
Martin Willisegger. The original copyright notices and the license are preserved.

## Credits

- **Gohan ZQL**: (c) 2026 Esteban Monge - Sempai Space
- **NagiosQL**: (c) 2005-2023 Martin Willisegger and the NagiosQL contributors (original author and project)
- **Claude (Anthropic)**: AI assistant that helped to develop Gohan ZQL (template engine, front-end libraries,
  rebranding)

### Third-party components

| Component | Purpose | License |
|-----------|---------|---------|
| [Twig](https://twig.symfony.com/) | template engine (`libraries/vendor`) | BSD-3-Clause |
| [TinyMCE](https://www.tiny.cloud/) 8 | help text editor (`functions/tinyMCE`) | GPL-2.0-or-later |
| [YUI](https://github.com/canonical/yui) 3 (Canonical maintained fork) | dialogs, calendar, tabs (`functions/yui`) | BSD-3-Clause |

## Installation

Requires PHP 8.1 or newer, a web server and a MySQL/MariaDB database. Twig is bundled, Composer is not needed.
See `doc/INSTALLATION_enGB.txt` (English) or `doc/INSTALLATION_deDE.txt` (German).

## Notes about the fork

- Only the visible product name was changed. Technical names such as the default database name, the default
  configuration paths (`/etc/nagiosql`) and the PHP class names still use the original NagiosQL names, so that
  installations stay compatible with NagiosQL.
- The website and repository links in the file headers, the donation button, the translation service link and the
  online version check still point to the upstream NagiosQL project until Gohan ZQL has its own.
- See `doc/CHANGELOG` for the changes.

## License

Gohan ZQL is free software, distributed under the terms of the GNU General Public License. See `LICENSE`.
