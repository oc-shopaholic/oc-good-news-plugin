# Good News

[![Build Status](https://travis-ci.org/lovata/oc-good-news-plugin.svg?branch=master)](https://travis-ci.org/lovata/oc-good-news-plugin)
[![Coverage Status](https://coveralls.io/repos/github/lovata/oc-good-news-plugin/badge.svg?branch=master)](https://coveralls.io/github/lovata/oc-good-news-plugin?branch=master)
[![Maintainability](https://api.codeclimate.com/v1/badges/e0d44449f4ea93a1da01/maintainability)](https://codeclimate.com/github/lovata/oc-good-news-plugin/maintainability)[![Crowdin](https://d322cqt584bo4o.cloudfront.net/good-news-for-october-cms/localized.svg)](https://crowdin.com/project/good-news-for-october-cms)
[![SemVer](http://img.shields.io/SemVer/2.0.0.png)](http://semver.org/spec/v2.0.0.html)
[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

Simple blogging plugin for October CMS developed by [LOVATA](https://lovata.com).

![Good News Banner](assets/images/good-news-banner.png)

## Overview

Good News is a simple and easy-to-use blogging plugin. It's provided free of charge and includes the following set of features:

* Managing posts
* Managing categories
* Adding categories list to the site navigation
* Adding latest posts block to the page
* Adding filtered category block to the page

> Please note, the architecture of the plugin allows [extending](https://octobercms.com/docs/plugin/extending) the existing methods, fields and other data without interfering with original source code!

The development of Good News plugin is guided by the similar philosophies of October CMS and Unix like operating systems, where the main focus is to create simple microarchitecture solutions that communicate with each other through smart APIs.

One one hand, this approach allows keeping performance, security, and functionality of the code to a high standard. On the other hand, it provides a clean and smooth back-end UI/UX that isn't over-bloated with the features.

## Installation

Regardless of the installation type you choose, you must install [Toolbox plugin](https://octobercms.com/plugin/lovata-toolbox), which is a required dependency for Shopaholic.

### Artisan

Using the Laravel’s CLI is the fastest way to get started. Just run the following commands in a project’s root directory:

```bash
php artisan plugin:install lovata.toolbox
php artisan plugin:install lovata.goodnews
```

## Documentation

The complete official documentation of the ecosystem can be found [here](https://github.com/lovata/oc-good-news-plugin/wiki).

## Quality standards

We ensure the high quality of our plugins and provide you with full support. All of our plugins have extensive documentation. The quality of our plugins goes through rigorous testing, we have launched automated testing for all of our plugins. Our code conforms with the best writing and structuring practices.  All this guarantees the stable work of our plugins after they are updated with new functionality and ensures their smooth integration.

## Get involved

If you're interested in the improvement of this project you can help in the following ways:
* bug reporting and new feature requesting by creating issues on plugin [GitHub page](https://github.com/lovata/oc-good-news-plugin/issues);
* contribution to a project following these [instructions](https://github.com/lovata/oc-good-news-plugin/blob/master/CONTRIBUTING.md);
* localization to your language using [Crowdin](https://crowdin.com/project/good-news-for-october-cms) service.

Let us know if you have any other questions, ideas or suggestions! Just drop a line at octobercms@lovata.com.

## License

© 2019, [LOVATA Group, LLC](https://github.com/lovata) under [GNU GPL v3](https://opensource.org/licenses/GPL-3.0).

Developed by [Andrey Kharanenka](https://github.com/kharanenka).
