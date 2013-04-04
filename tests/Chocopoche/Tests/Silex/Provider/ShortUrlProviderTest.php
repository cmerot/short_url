<?php
namespace Chocopoche\Tests\Silex\Provider;

// use Silex\Application;
// use Chocopoche\Silex\Provider\ShortUrlProvider;

/**
 * ShortUrlProvider test cases.
 */
class ShortUrlProviderTest extends \PHPUnit_Framework_TestCase
{
   public function testerPushEtPop()
    {
        $pile = array();
        $this->assertEquals(0, count($pile));
 
        array_push($pile, 'foo');
        $this->assertEquals('foo', $pile[count($pile)-1]);
        $this->assertEquals(1, count($pile));
 
        $this->assertEquals('foo', array_pop($pile));
        $this->assertEquals(0, count($pile));
    }
}
