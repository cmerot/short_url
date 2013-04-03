<?php
require_once __DIR__.'/../vendor/autoload.php';
use Silex\Application;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\TranslationServiceProvider; // required by the form service in default templates
use Silex\Provider\DoctrineServiceProvider;
use Chocopoche\Silex\Provider\ShortUrlProvider;
use Chocopoche\Silex\Provider\GoogleOauth2Provider;

// Seems required by Datetime
date_default_timezone_set('UTC');

// App service provider setup
$app = new Application();
$app['debut'] = true;
$app->register(new UrlGeneratorServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../src/Chocopoche/Silex/View',
));
$app->register(new TranslationServiceProvider());
$app->register(new DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_sqlite',
        'path'     => __DIR__.'/../db/app.db',
    ),
));
$oauth = new GoogleOauth2Provider;
$app->register($oauth, array(
    'google_oauth.client_id'     => $google_oauth_client_id,
    'google_oauth.client_secret' => $google_oauth_client_secret,
));
$app->mount('/', $oauth);

// Short URL mountpoint
$short_url = new ShortUrlProvider;
$short_url_mountpoint = '/';

$app->register($short_url, array(
    'short_url.alphabet'    => '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ',
    'short_url.mountpoint'  => $short_url_mountpoint,
));

$app->mount($short_url_mountpoint, $short_url);

return $app;
