<?php
namespace Chocopoche\Tests\Silex\Provider;

use Silex\WebTestCase;
use Silex\Provider\SessionServiceProvider;

/**
 * ShortUrlProvider test cases.
 */
class ShortUrlProviderTest extends WebTestCase
{
   public function testController()
    {
        $client = $this->createClient();

        $client->request('get', '/');
        $this->assertContains('Last shortened URL', $client->getResponse()->getContent());

        $client->request('get', '/mine/');
        $this->assertContains('You must be authenticated to access this page.', $client->getResponse()->getContent());
    }

   public function testService()
    {
        $app = $this->app;
        $this->assertInstanceOf('Chocopoche\Silex\Model\ShortUrlModel', $app['short_url']);
        $this->assertInstanceOf('Symfony\Component\Form\Form', $app['short_url.form']);
    }

    public function createApplication()
    {
        $app = require __DIR__ . '/../../../../../app/bootstrap.php';
        $app->register(new SessionServiceProvider(), array(
            'session.test' => true,
        ));
        return $app;
    }
}
