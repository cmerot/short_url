<?php
namespace Chocopoche\Tests\Silex\Model;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Doctrine\Common\EventManager;
use Chocopoche\Math\Bijection;
use Chocopoche\Silex\Model\ShortUrlModel;


class ShortUrlModelTest extends \PHPUnit_Framework_TestCase
{
    protected $conn;

    public function testMethods()
    {
        date_default_timezone_set('UTC');
        $this->initDatabase();
        $encoder = new Bijection('01');
        $model   = new ShortUrlModel($this->conn, $encoder);
        $model->importSchema();

        // 4 rows in the url table
        $id1 = $model->add('http://example.com',   'u@example.com');
        $id2 = $model->add('http://example.com/2', 'u@example.com');
        $id3 = $model->add('http://example.com/3', 'u2@example.com');
        $id4 = $model->add('http://example.com/4');

        $url4 = $model->getById($id4);
        $this->assertEquals('http://example.com/4', $url4['url']);
        $this->assertEquals(11100100111110111100000001100, $url4['short_code']);

        $last_shorten = $model->getLastShorten();
        $this->assertEquals(1, count($last_shorten));

        $last_shorten = $model->getLastShorten(10);
        $this->assertEquals(4, count($last_shorten));

        $last_shorten = $model->getLastShorten(10, 'u@example.com');
        $this->assertEquals(2, count($last_shorten));

        // Increment and last redirects tests
        $model->incrementCounter($id1);
        for ($i=0; $i < 15; $i++) $model->incrementCounter($id4);

        // Only one redirect
        $last_redirects = $model->getLastRedirects($id1);
        $this->assertEquals(1, count($last_redirects));
        $redirect_counter = $model->getRedirectCounter($id1);
        $this->assertEquals(1, $redirect_counter);

        // No redirects
        $last_redirects = $model->getLastRedirects($id2);
        $this->assertEquals(0, count($last_redirects));
        $redirect_counter = $model->getRedirectCounter($id2);
        $this->assertEquals(0, $redirect_counter);

        // Returns only last 10
        $last_redirects = $model->getLastRedirects($id4);
        $this->assertEquals(10, count($last_redirects));
        $redirect_counter = $model->getRedirectCounter($id4);
        $this->assertEquals(15, $redirect_counter);
    }

    protected function initDatabase() {
        $connectionParams = array(
            'driver'   => 'pdo_sqlite',
            'path'     => ':memory:',
        );
        $config     = new Configuration();
        $this->conn = DriverManager::getConnection($connectionParams, $config);
    }
}
