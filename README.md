# Url shortener application

[![Build Status](https://travis-ci.org/chocopoche/short_url.png?branch=master)](https://travis-ci.org/chocopoche/short_url)

Yet another url shortener built for Silex's PHP framework!

## Install

First, install the package and its dependencies:

    $ git clone https://github.com/chocopoche/short_url.git
    $ cd short_url
    $ curl -sS https://getcomposer.org/installer | php
    $ php composer.phar update

You may have to fix perms on the vendor/google directory, as its not world
readable after extracting the tarball.

    $ chmod -R a+rX vendor/google

Then creates the database, here is how to do so with an sqlite database:

    $ app/console db:create
    $ app/console db:import-schema

To run tests:

    $ vendor/bin/phpunit

To let users authenticate themselves to keep track of their urls, enable
Google OAuth2 by creating an application on the [console](https://code.google.com/apis/console/).

Configure your database in `app/bootstrap.php` and your GoogleOAuth client id
and secret in `config/app.php`.

## Features

Url slugs are generated with the SQL row id integer, which is hashed with a
*bidirectional encryption (Feistel cipher) that maps the integer space onto
itself*. Pasted from [https://gist.github.com/baldurrensch/3710618](https://gist.github.com/baldurrensch/3710618)

The resulting integer is then mapped with a bijective algorithm that uses a
configurable alphabet. Those functions are pasted from [http://www.flickr.com/groups/api/discuss/72157616713786392/](http://www.flickr.com/groups/api/discuss/72157616713786392/).

This way there is no need to store the short code in the database, and the short
codes generated are not obvious to decode just by reading them (I mean you
can't just increment the string to find the next record).

- /{short_code} redirects to the long url
- /{short_code}.png shows a QR Code
- /{short_code}/details shows the details available for the shortened url
- /last/ redirects to the last shortened url
- /shorten/ usefull with the help of the javascript bookmarklet
- /mine/ When you are authenticated, shorten urls will be associated with your
  account and you'll be able to see your last shorten urls.

**Bookmarklet**

HTML code to generate a link:

    <a href="javascript:location='http://tmb.io/shorten/?url='+encodeURIComponent(location.href); void 0" class="btn btn-success">Shorten</a>

Or just add a bookmark with the following url:

    javascript:location='http://tmb.io/shorten/?url='+encodeURIComponent(location.href); void 0

## Live demo

A live demo is available at [tmb.io](http://tmb.io).

## API Doc

Available at [tmb.io/doc/](http://tmb.io/doc/).
