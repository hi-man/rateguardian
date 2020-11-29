<?php

declare(strict_types=1);

namespace Tests;

use RateGuardian\RateGuardian;
use PHPUnit\Framework\TestCase;

final class RateGuardianTest extends TestCase
{
    private $guardian;

    protected function setUp(): void
    {
        $this->guardian = new RateGuardian();
    }

    public function testGuardKeyTooLong(): void
    {
        $ret = $this->guardian->guardianOn(
            '12345678901234567890123456789012345678901',
            1,
            1
        );

        $this->assertTrue($ret === true);

        $ret = $this->guardian->guardianOn(
            '123456789012345678901234567890123456789012',
            1,
            1
        );

        $this->assertTrue($ret === false);
    }

    public function testGuardTimeout(): void
    {
        $key = 'guduardiantesting';
        $total = 3;
        $ttl = 2;

        $this->guardian->guardianOn($key, $total, $ttl);

        $ret = $this->guardian->guard($key);
        $this->assertTrue($ret === true);
        $ret = $this->guardian->guard($key);
        $this->assertTrue($ret === true);
        $ret = $this->guardian->guard($key);
        $this->assertTrue($ret === true);

        sleep(2);
        $ret = $this->guardian->guard($key);
        $this->assertTrue($ret === true);
    }

    public function testGuarded(): void
    {
        $key = 'guduardiantesting';
        $total = 3;
        $ttl = 2;

        $this->guardian->guardianOn($key, $total, $ttl);

        $ret = $this->guardian->guard($key);
        $this->assertTrue($ret === true);
        $ret = $this->guardian->guard($key);
        $this->assertTrue($ret === true);
        $ret = $this->guardian->guard($key);
        $this->assertTrue($ret === true);

        $ret = $this->guardian->guard($key);
        $this->assertTrue($ret === false);
        $ret = $this->guardian->guard($key);
        $this->assertTrue($ret === false);
    }

    public function testGuardShow(): void
    {
        $key = 'guduardiantesting';
        $total = 3;
        $ttl = 2;

        $this->guardian->guardianOn($key, $total, $ttl);

        $ret = $this->guardian->guard($key);
        $this->assertTrue($ret === true);

        $ret = $this->guardian->guard($key);
        $this->assertTrue($ret === true);

        $ret = $this->guardian->show($key);
        $this->assertTrue(
            count($ret) === 4
            && $ret['total'] === $total
            && $ret['ttl'] === 2
            && isset($ret['expired'])
            && $ret['current'] === 2
        );
    }

    public function testGuardShowTimeout(): void
    {
        $key = 'guduardiantesting';
        $total = 3;
        $ttl = 2;

        $this->guardian->guardianOn($key, $total, $ttl);

        $ret = $this->guardian->guard($key);
        $this->assertTrue($ret === true);

        $ret = $this->guardian->guard($key);
        $this->assertTrue($ret === true);

        sleep(3);
        $ret = $this->guardian->show($key);
        $this->assertTrue(
            count($ret) === 4
            && $ret['total'] === $total
            && $ret['ttl'] === 2
            && isset($ret['expired'])
            && $ret['current'] === 0
        );
    }

    public function testGuardOff(): void
    {
        $key = 'guduardiantesting';
        $total = 3;
        $ttl = 2;

        $this->guardian->guardianOn($key, $total, $ttl);

        $ret = $this->guardian->guard($key);
        $this->assertTrue($ret === true);

        $ret = $this->guardian->guard($key);
        $this->assertTrue($ret === true);

        $this->guardian->guardianOff($key);

        $ret = $this->guardian->show($key);
        $this->assertTrue($ret === null);

        $ret = $this->guardian->guard($key);
        $this->assertTrue($ret === true);
    }
}
