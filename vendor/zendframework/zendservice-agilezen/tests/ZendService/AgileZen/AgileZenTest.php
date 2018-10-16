<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Service
 */

namespace ZendServiceTest\AgileZen;

use ZendService\AgileZen\AgileZen as AgileZenService;

class AgileZenTest extends \PHPUnit_Framework_TestCase
{
    protected static $projectId;

    public function setUp()
    {
        if (!constant('TESTS_ZEND_SERVICE_AGILEZEN_ONLINE_ENABLED')) {
            self::markTestSkipped('ZendService\AgileZen tests are not enabled');
        }
        if(!defined('TESTS_ZEND_SERVICE_AGILEZEN_ONLINE_APIKEY')) {
            self::markTestSkipped('The ApiKey costant has to be set.');
        }
        $this->agileZen = new AgileZenService(constant('TESTS_ZEND_SERVICE_AGILEZEN_ONLINE_APIKEY'));
    }
    public function testNoKeyException()
    {
        $this->setExpectedException(
            'ZendService\AgileZen\Exception\InvalidArgumentException',
            'You need an API key to use AgileZen'
        );
        $this->agileZen = new AgileZenService('');
    }
    public function testAuthenticate()
    {
        $this->assertTrue($this->agileZen->authenticate());
    }
    public function testWrongAuthentication()
    {
        $this->agileZen = new AgileZenService('wrongApi');
        $this->assertFalse($this->agileZen->authenticate());
    }
}
