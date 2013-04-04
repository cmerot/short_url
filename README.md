# Url shortener application

[![Build Status](https://travis-ci.org/chocopoche/short_url.png?branch=master)](https://travis-ci.org/chocopoche/short_url)

Yet another url shortener built for Silex's PHP framework!

## Install

First, install the package and its dependencies: 

    $ git clone https://github.com/chocopoche/short_url.git
    $ cd short_url
    $ curl -sS https://getcomposer.org/installer | php
    $ php composer.phar update

You can also directly use composer:

    $ curl -sS https://getcomposer.org/installer | php
    $ php composer.phar create-project -s dev chocopoche/short_url PATH/TO/YOUR/APP

You may have to fix perms on the vendor/google directory, as its not world 
readable after extracting the tarball.

    $ chmod -R a+rX vendor/google

Then creates the database, here is how to do so with an sqlite database:

    $ mkdir db
    $ sqlite3 db/app.db < short_url.sql
    $ chmod -R a+w db

To let users authenticate themselves to keep track of their urls, enable
Google OAuth2 by creating an application on the [console](https://code.google.com/apis/console/).

Configure your database in `app/bootstrap.php` and your GoogleOAuth client id
and secret in `app/config.php`.

## Features

Url slugs are generated with a bijective algorythm, directly pasted from 
[http://www.flickr.com/groups/api/discuss/72157616713786392/](http://www.flickr.com/groups/api/discuss/72157616713786392/).

- /{short_code} redirects to the long url
- /{short_code}.png shows a QR Code
- /{short_code}/details shows the details available for the shortened url
- /last/ redirects to the last shortened url
- /shorten/ usefull with the help of the javascript bookmarklet
- /mine/ When you are authenticated, shorten urls will be associated with your account and you'll be able to see your last shorten urls.

**Bookmarklet** 

HTML code to generate a link:

    <a href="javascript:location='http://tmb.io/shorten/?url='+encodeURIComponent(location.href); void 0" class="btn btn-success">Shorten</a>

Or just add a bookmark with the following url:

    javascript:location='http://tmb.io/shorten/?url='+encodeURIComponent(location.href); void 0

## Live demo

A live demo is available at [tmb.io](http://tmb.io).

## Todo

- testing + travis
- assetic + console
- ...

