# CsvMigrations plugin for CakePHP

[![Build Status](https://travis-ci.org/QoboLtd/cakephp-csv-migrations.svg?branch=master)](https://travis-ci.org/QoboLtd/cakephp-csv-migrations)
[![Latest Stable Version](https://poser.pugx.org/qobo/cakephp-csv-migrations/v/stable)](https://packagist.org/packages/qobo/cakephp-csv-migrations)
[![Total Downloads](https://poser.pugx.org/qobo/cakephp-csv-migrations/downloads)](https://packagist.org/packages/qobo/cakephp-csv-migrations)
[![Latest Unstable Version](https://poser.pugx.org/qobo/cakephp-csv-migrations/v/unstable)](https://packagist.org/packages/qobo/cakephp-csv-migrations)
[![License](https://poser.pugx.org/qobo/cakephp-csv-migrations/license)](https://packagist.org/packages/qobo/cakephp-csv-migrations)
[![codecov](https://codecov.io/gh/QoboLtd/cakephp-csv-migrations/branch/master/graph/badge.svg)](https://codecov.io/gh/QoboLtd/cakephp-csv-migrations)

## About

CakePHP 3+ plugin for easy creation of application modules, based on a
variety of the configuration files (CSV/INI/JSON).

Developed by [Qobo](https://www.qobo.biz), used in [Qobrix](https://qobrix.com).

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

This plugin extends [CakePHP 3 Migrations plugin](https://github.com/cakephp/migrations).

The recommended way to install composer packages is:

```
composer require qobo/cakephp-csv-migrations
```

## Setup
Load plugin
```
bin/cake plugin load --bootstrap CsvMigrations
```

Load required plugin(s)
```
bin/cake plugin load Muffin/Trash
```

## Documentation

Have a look at [doc/CSV.md](doc/CSV.md) for details of what is supported.
