<?php
namespace Chocopoche\Tests\Silex\Provider;

use Silex\WebTestCase;
use Silex\Application;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Chocopoche\Silex\Provider\GoogleOauth2Provider;

/**
 * ShortUrlProvider test cases.
 */
class GoogleOauth2ProviderTest extends WebTestCase
{
   public function testPages()
    {
        $client = $this->createClient();

        // Login OAuth redirection
        $client->request('get', '/connect/');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertStringStartsWith('https://accounts.google.com/o/oauth2/auth?', $client->getResponse()->headers->get('Location'));

        // Logout page
        $client->request('get', '/logout/');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertEquals('/', $client->getResponse()->headers->get('Location'));

        // Logout page without token
        $client->request('get', '/oauth2callback/');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertContains("Nothing here!", $client->getResponse()->getContent());

        // Logout page with invalid token
        $client->request('get', '/oauth2callback/?code=foobar');
        $this->assertEquals(500, $client->getResponse()->getStatusCode());
        $this->assertContains("Error fetching OAuth2 access token", $client->getResponse()->getContent());
    }

    public function testService()
    {
        $app = $this->app;
        $this->assertInstanceOf('Google_Client', $app['google_oauth.client']);
        $this->assertInstanceOf('Google_Oauth2Service', $app['google_oauth.service']);
    }


    public function createApplication()
    {
        $app = new Application();
        $app['debug'] = true;
        $app->register(new UrlGeneratorServiceProvider());
        $app->register(new SessionServiceProvider(), array(
            'session.test' => true,
        ));
        $oauth = new GoogleOauth2Provider;
        $app->register($oauth, array(
            'google_oauth.client_id'     => 'foo',
            'google_oauth.client_secret' => 'bar',
        ));
        $app->mount('/', $oauth);
        return $app;
    }
}
