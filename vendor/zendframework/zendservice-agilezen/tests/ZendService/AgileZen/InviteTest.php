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

class InviteTest extends \PHPUnit_Framework_TestCase
{
    protected static $inviteId;

    public function setUp()
    {
        if (!constant('TESTS_ZEND_SERVICE_AGILEZEN_ONLINE_ENABLED')) {
            self::markTestSkipped('ZendService\AgileZen tests are not enabled');
        }
        if(!defined('TESTS_ZEND_SERVICE_AGILEZEN_ONLINE_APIKEY')) {
            self::markTestSkipped('The ApiKey costant has to be set.');
        }
        if(!defined('TESTS_ZEND_SERVICE_AGILEZEN_ONLINE_PROJECT_ID')) {
            self::markTestSkipped('The project ID costant has to be set.');
        }
        $this->agileZen = new AgileZenService(constant('TESTS_ZEND_SERVICE_AGILEZEN_ONLINE_APIKEY'));
    }
    public function testAddInvite()
    {
        if (constant('TESTS_ZEND_SERVICE_AGILEZEN_ONLINE_INVITE_EMAIL')==='') {
            $this->markTestSkipped('No invitation email specified');
        }
        if (constant('TESTS_ZEND_SERVICE_AGILEZEN_ONLINE_INVITE_ROLE_ID')==='') {
            $this->markTestSkipped('No role id invitation specified');
        }
        $data = array (
            'email' => constant('TESTS_ZEND_SERVICE_AGILEZEN_ONLINE_INVITE_EMAIL'),
            'role'  => (integer) constant('TESTS_ZEND_SERVICE_AGILEZEN_ONLINE_INVITE_ROLE_ID')
        );
        $invite = $this->agileZen->addInvite(
                constant('TESTS_ZEND_SERVICE_AGILEZEN_ONLINE_PROJECT_ID'),
                $data
        );
        $this->assertTrue($this->agileZen->isSuccessful());
        if (!empty($invite)) {
            $this->assertTrue($invite instanceof \ZendService\AgileZen\Resources\Invite);
            $this->assertEquals(constant('TESTS_ZEND_SERVICE_AGILEZEN_ONLINE_INVITE_EMAIL'), $invite->getEmail());
            self::$inviteId = $invite->getId();
        }
    }
    public function testGetInvites()
    {
        $invites = $this->agileZen->getInvites(
                constant('TESTS_ZEND_SERVICE_AGILEZEN_ONLINE_PROJECT_ID')
        );
        $this->assertTrue($this->agileZen->isSuccessful());
        if (empty($invites)) {
            $this->markTestSkipped('No invites founded for the project Id ' .
                    constant('TESTS_ZEND_SERVICE_AGILEZEN_ONLINE_PROJECT_ID'));
        }
        $this->assertTrue($invites instanceof \ZendService\AgileZen\Container);
        foreach ($invites as $invite) {
            $this->assertTrue($invite instanceof \ZendService\AgileZen\Resources\Invite);
        }
    }
    public function testGetInvite()
    {
        if (empty(self::$inviteId)) {
            $this->markTestSkipped('No invite founded for the project Id ' .
                    constant('TESTS_ZEND_SERVICE_AGILEZEN_ONLINE_PROJECT_ID'));
        }
        $invite = $this->agileZen->getInvite(
                constant('TESTS_ZEND_SERVICE_AGILEZEN_ONLINE_PROJECT_ID'),
                self::$inviteId
        );
        $this->assertTrue($this->agileZen->isSuccessful());
        $this->assertTrue($invite instanceof \ZendService\AgileZen\Resources\Invite);
        $this->assertEquals(self::$inviteId, $invite->getId());
        $this->assertEquals(constant('TESTS_ZEND_SERVICE_AGILEZEN_ONLINE_INVITE_EMAIL'), $invite->getEmail());
    }
    public function testRemoveInvite()
    {
        if (empty(self::$inviteId)) {
            $this->markTestSkipped('No invite to remove for the project Id ' .
                    constant('TESTS_ZEND_SERVICE_AGILEZEN_ONLINE_PROJECT_ID'));
        }
        $result = $this->agileZen->removeInvite(
                constant('TESTS_ZEND_SERVICE_AGILEZEN_ONLINE_PROJECT_ID'),
                self::$inviteId
        );
        $this->assertTrue($this->agileZen->isSuccessful());
        $this->assertTrue($result);
    }
}
