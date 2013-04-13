<?php
namespace Chocopoche\Silex\Provider;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Google OAuth2 service and controller provider
 */
class GoogleOauth2Provider implements ServiceProviderInterface, ControllerProviderInterface
{
    /**
     * @see ServiceProviderInterface::register
     */
    public function register(Application $app)
    {
        $app['google_oauth.client'] = $app->share(function () use ($app) {
            $client = new \Google_Client();
            $client->setClientId($app['google_oauth.client_id']);
            $client->setClientSecret($app['google_oauth.client_secret']);

            return $client;
        });
        $app['google_oauth.service'] = $app->share(function () use ($app) {
            $oauth2 = new \Google_Oauth2Service($app['google_oauth.client']);
            return $oauth2;
        });

    }

    /**
     * @see ServiceProviderInterface::boot
     */
    public function boot(Application $app) {
    }

    /**
     * All the controllers for the application:
     *
     * - /logout/: remove the token + revoke google oauth2
     * - /connect/: redirects to the google oauth2 authorisation page
     * - /oauth2callback/: the oauth2 callback in which we write the token to
     *   the cookie
     *
     * @see ControllerProviderInterface::connect
     */
    public function connect(Application $app)
    {
        $app->before(function () use ($app) {
            $client = $app['google_oauth.client'];
            $oauth2 = $app['google_oauth.service'];

            // The redirect uri can only be generated when the request is available
            // which is not in the service registering process.
            $client->setRedirectUri($app['url_generator']->generate('google_oauth_callback', array(), true));

            if ($token = $app['session']->get('token')) {
                $client->setAccessToken($token);
            }
            if ($client->getAccessToken()) {
                $user = $oauth2->userinfo->get();
                $app['user'] = $user;
                $app['session']->set('token', $client->getAccessToken());
                $app['google_oauth_user_email'] = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
                $app['google_oauth_user_picture'] = filter_var($user['picture'], FILTER_VALIDATE_URL);
            } else {
                $app['user'] = false;
            }
        });

        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->get('/logout/', function (Request $request) use ($app) {
            $client = $app['google_oauth.client'];
            $app['session']->remove('token');
            $client->revokeToken();
            return $app->redirect('/');
        })
        ->bind('google_oauth_logout');

        // Connect
        $controllers->get('/connect/', function (Request $request) use ($app) {
            $client = $app['google_oauth.client'];
            if ($client->getAccessToken())
                $url = '/';
            else
                $url = $client->createAuthUrl();

            return $app->redirect($url);
        })
        ->bind('google_oauth_connect');

        // Oauth2 callback
        $controllers->get('/oauth2callback/', function (Request $request) use ($app) {
            $client = $app['google_oauth.client'];
            if ($request->get('code')) {
                $client->authenticate($request->get('code'));
                $app['session']->set('token', $client->getAccessToken());
                return $app->redirect('/');
            }
            $app->abort(404, "Nothing here!");
        })
        ->bind('google_oauth_callback');

        return $controllers;
    }
}
