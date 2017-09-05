<?php
namespace sngrl\PhpFirebaseCloudMessaging\Tests;

use PHPUnit\Framework\TestCase;

class PhpFirebaseCloudMessagingTestCase extends TestCase
{
    protected function tearDown()
    {
        \Mockery::close();
        parent::tearDown();
    }
}