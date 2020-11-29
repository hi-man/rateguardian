<?php

declare(strict_types=1);

namespace Tests;

use RateGuardian\RateGuardian;
use PHPUnit\Framework\TestCase;

final class RateGuardianInstanceTest extends TestCase
{
    public function testGuardKeyTooLong(): void
    {
        $ret = RateGuardian::getInstance()->guardianOn(
            '12345678901234567890123456789012345678901',
            1,
            1
        );

        $this->assertTrue($ret === true);

        $ret = RateGuardian::getInstance()->guardianOn(
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

        RateGuardian::getInstance()->guardianOn($key, $total, $ttl);

        $ret = RateGuardian::getInstance()->guard($key);
        $this->assertTrue($ret === true);
        $ret = RateGuardian::getInstance()->guard($key);
        $this->assertTrue($ret === true);
        $ret = RateGuardian::getInstance()->guard($key);
        $this->assertTrue($ret === true);

        sleep(2);
        $ret = RateGuardian::getInstance()->guard($key);
        $this->assertTrue($ret === true);
    }

    public function testGuarded(): void
    {
        $key = 'guduardiantesting';
        $total = 3;
        $ttl = 2;

        RateGuardian::getInstance()->guardianOn($key, $total, $ttl);

        $ret = RateGuardian::getInstance()->guard($key);
        $this->assertTrue($ret === true);
        $ret = RateGuardian::getInstance()->guard($key);
        $this->assertTrue($ret === true);
        $ret = RateGuardian::getInstance()->guard($key);
        $this->assertTrue($ret === true);

        $ret = RateGuardian::getInstance()->guard($key);
        $this->assertTrue($ret === false);
        $ret = RateGuardian::getInstance()->guard($key);
        $this->assertTrue($ret === false);
    }

    public function testGuardShow(): void
    {
        $key = 'guduardiantesting';
        $total = 3;
        $ttl = 2;

        RateGuardian::getInstance()->guardianOn($key, $total, $ttl);

        $ret = RateGuardian::getInstance()->guard($key);
        $this->assertTrue($ret === true);

        $ret = RateGuardian::getInstance()->guard($key);
        $this->assertTrue($ret === true);

        $ret = RateGuardian::getInstance()->show($key);
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

        RateGuardian::getInstance()->guardianOn($key, $total, $ttl);

        $ret = RateGuardian::getInstance()->guard($key);
        $this->assertTrue($ret === true);

        $ret = RateGuardian::getInstance()->guard($key);
        $this->assertTrue($ret === true);

        sleep(3);
        $ret = RateGuardian::getInstance()->show($key);
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

        RateGuardian::getInstance()->guardianOn($key, $total, $ttl);

        $ret = RateGuardian::getInstance()->guard($key);
        $this->assertTrue($ret === true);

        $ret = RateGuardian::getInstance()->guard($key);
        $this->assertTrue($ret === true);

        RateGuardian::getInstance()->guardianOff($key);

        $ret = RateGuardian::getInstance()->show($key);
        $this->assertTrue($ret === null);

        $ret = RateGuardian::getInstance()->guard($key);
        $this->assertTrue($ret === true);
    }
}
