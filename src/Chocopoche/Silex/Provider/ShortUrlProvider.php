<?php
namespace Chocopoche\Silex\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Chocopoche\Silex\Model\ShortUrlModel;
use Chocopoche\Math\Bijection;
use PHPQRCode\QRcode;

/**
 * Short url service and controller provider
 */
class ShortUrlProvider implements ServiceProviderInterface, ControllerProviderInterface
{

    /**
     * @see ServiceProviderInterface::register
     */
    public function register(Application $app)
    {
        // Collection of function to access database
        $app['short_url'] = $app->share(function () use ($app) {
            $bijection  = new Bijection($app['short_url.alphabet']);
            return new ShortUrlModel($app['db'], $bijection);
        });

        // The form to shorten an url
        $app['short_url.form'] = $app->share(function () use ($app) {
            return $app['form.factory']->createBuilder('form', null, array(
                    'csrf_protection' => ! isset($app['console']),
            ))
                ->add('url', 'text', array(
                    'attr'  => array(
                        'placeholder'   => 'Paste your URL here',
                        'class'         => 'input-small',
                        'size'          => 140,
                    ),
                    'label' => 'Paste your URL here',
                    'constraints' => new Assert\Url(),
                ))
                ->getForm();
        });
    }

    /**
     * @see ServiceProviderInterface::boot
     */
    public function boot(Application $app) {  }


    /**
     * @see ControllerProviderInterface::connect
     */
    public function connect(Application $app) 
    {

        // Global layout
        $app->before(function () use ($app) {
            $app['twig']->addGlobal('layout', $app['twig']->loadTemplate('layout.twig'));
        });

        // Error management
        $app->error(function (\Exception $e, $code) use ($app) {
            if ($code >= 400 && $code < 500)
                $message = $e->getMessage();
            else
                $message = 'Whoops, looks like something went wrong.';

            // In case twig goes wrong, exemple: no route found means the 
            // $app->before() wont be executed
            try {
                $app['user'] = false;
                $app['twig']->addGlobal('layout', $app['twig']->loadTemplate('layout.twig'));
                return $app['twig']->render('error.twig', array(
                    'message' => $message,
                    'code'    => $code,
                ));
            } catch (\Exception $e) {
                return new Response('Whoops, looks like something went very wrong.', $code);        
            }
        });

        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        // Homepage + form handler
        $controllers->get('/', function (Request $request) use ($app) {

            return $app['twig']->render('index.twig', array(
                'form' => $app['short_url.form']->createView(),
                'last' => $app['short_url']->getLastShorten(10),
            ));
        })
        ->bind('short_url_homepage');

        // Handle the form submission
        $controllers->post('/', function (Request $request) use ($app) {
            $form = $app['short_url.form'];
            $form->bind($request);

            if ($form->isValid()) {
                $data = $form->getData();
                $email = $app['user']['email'] ? $app['user']['email'] : null;
                $id = $app['short_url']->add($data['url'], $email);
                $url_details = $app['short_url']->getById($id);
                $r_url = $app['url_generator']->generate('short_url_details', array('short_code' => $url_details['short_code']));

                return $app->redirect($r_url);
            } 
            else {
                return $app['twig']->render('index.twig', array(
                    'form' => $form->createView(),
                    'last' => $app['short_url']->getLastShorten(10),
                ));
            }

            $app->abort(404, "Nothing found!");
        });

        // Details
        $controllers->get('/{short_code}/details', function ($short_code) use ($app) {
            $url_details        = $app['short_url']->getByShortCode($short_code);
            $last_redirects     = $app['short_url']->getLastRedirects($url_details['id']);
            $redirects_counter  = $app['short_url']->getRedirectCounter($url_details['id']);

            return $app['twig']->render('details.twig', array(
                'long_url'          => $url_details['url'],
                'short_code'        => $short_code,
                'last_redirects'    => $last_redirects,
                'redirects_counter' => $redirects_counter,
            ));
        })
        ->bind('short_url_details');

        // QRCode
        $controllers->get('/{short_code}.png', function ($short_code) use ($app) {
            $url_details = $app['short_url']->getByShortCode($short_code);

            if ($url_details) {
                $short_url   = $app['url_generator']->generate('short_url_redirect', array('short_code' => $short_code), true);
                $file        = $_SERVER['DOCUMENT_ROOT'] . "/../cache/$short_code.png";

                if (!file_exists($file)) {
                    QRcode::png($short_url, $file, 'L', 4, 2);
                }

                $stream = function() use ($file) { readfile($file); };

                return $app->stream($stream, 200, array('Content-Type' => 'image/png'));
            }

            $app->abort(404, "That shorten url does not exist!");
        })
        ->bind('short_url_qrcode');

        // Shorten the url in the query string (?url=)
        $controllers->get('/shorten/', function (Request $request) use ($app) {
            $url = rawurldecode($request->get('url'));
            $errors = $app['validator']->validateValue($url, new Assert\Url());
            if ($url && ! $errors->has(0)) {
                $id = $app['short_url']->add($url);
                $url_details = $app['short_url']->getById($id);
                $r_url = $app['url_generator']->generate('short_url_details', array('short_code' => $url_details['short_code']));

                return $app->redirect($r_url);
            } 
            else {
                $app->abort(404, $errors->get(0)->getMessage());
            }

            $app->abort(404, "The url query string parameter is required.");
        })
        ->bind('short_url_shorten');

        // Redirects to the last shorten url
        $controllers->get('/last/', function () use ($app) {
            $urls = $app['short_url']->getLastShorten(1);

            if ($urls[0]['id']) {
                $app['short_url']->incrementCounter($urls[0]['id']);

                return $app->redirect($urls[0]['url']);
            }

            $app->abort(404, "Nothing found!");
        })
        ->bind('short_url_last');

        // Redirects to the corresponding url
        $controllers->get('/{short_code}', function ($short_code) use ($app) {
            $url = $app['short_url']->getByShortCode($short_code);

            if ($url) {
                $app['short_url']->incrementCounter($url['id']);

                return $app->redirect($url['url']);
            }

            $app->abort(404, "That shorten url does not exist!");
        })
        ->bind('short_url_redirect');

        // User's last shorten urls
        $controllers->get('/mine/', function () use ($app) {
            if (!$app['user']['email']) 
                $app->abort(401, "You must be authenticated to access this page.");

            return $app['twig']->render('mine.twig', array(
                'last' => $app['short_url']->getLastShorten(10, $app['user']['email']),
            ));
        })
        ->bind('short_url_mine');

        return $controllers;
    }
}
