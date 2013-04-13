<?php
require_once __DIR__.'/../vendor/autoload.php';
use Silex\Application;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\TranslationServiceProvider; // required by the form service in default templates
use Silex\Provider\DoctrineServiceProvider;
use Chocopoche\Silex\Provider\ShortUrlProvider;
use Chocopoche\Silex\Provider\GoogleOauth2Provider;
use Symfony\Component\Yaml\Parser;
$yaml = new Parser();

$config = $yaml->parse(file_get_contents(__DIR__ . '/../config/parameters.yml'));

// TODO a better way to code sqlite db path
if ($config['databases']['short_url']['driver'] == 'pdo_sqlite') {
  $config['databases']['short_url']['path'] = __DIR__ . '/../' . $config['databases']['short_url']['path'];
}

// TODO move to the related class, with a check to avoid overriding
date_default_timezone_set('UTC');

// App service provider setup
$app = new Application();
$app['debug'] = $config['debug'];
$app->register(new UrlGeneratorServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new TranslationServiceProvider());
$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../src/templates',
));
$app->register(new DoctrineServiceProvider(), array('db.options' => $config['databases']['short_url']));

$oauth = new GoogleOauth2Provider;
$app->register($oauth, array(
    'google_oauth.client_id'     => $config['google_oauth']['client_id'],
    'google_oauth.client_secret' => $config['google_oauth']['client_secret'],
))->mount($config['google_oauth']['mountpoint'], $oauth);

$short_url = new ShortUrlProvider;
$app->register($short_url, array(
    'short_url.alphabet'    => $config['short_url']['alphabet'],
    'short_url.mountpoint'  => $config['short_url']['mountpoint'],
))->mount($config['short_url']['mountpoint'], $short_url);

return $app;
