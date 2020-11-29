<?php

/**
 * Rate Guardian
 *
 * Keep your server calm down
 */

declare(strict_types=1);

namespace RateGuardian;

use FlorianWolters\Component\Util\Singleton\SingletonTrait;

class RateGuardian
{
    use SingletonTrait;

    public const GUARDIAN_KEY_PREFIX = 'grdn_';
    public const GUARDIAN_SETTING = 's_';
    public const GUARDIAN_RATE = 'r_';

    // Yac_MAX_KEY_LEN - strlen(self::GUARDIAN_SETTING) - strlen(self::GUARDIAN_KEY_PREFIX)
    public const GUARDIAN_KEY_LEN_MAX = 41;

    private $guardian;

    public function __construct()
    {
        if (!extension_loaded('yac')) {
            thorow \InvalidArgumentException('yac extension not installed');
        }

        $this->guardian = new \Yac(self::GUARDIAN_KEY_PREFIX);
    }

    /**
     * initialize guardian key
     *
     * @param string $key the unique guardian key
     * @param int $total maximum guard counts in $ttl seconds
     * @param int $ttl the period in seconds
     *
     * @return false on failed, and true on success
     */
    public function guardianOn(string $key, int $total, int $ttl): bool
    {
        if (
            empty($key)
            || strlen($key) > self::GUARDIAN_KEY_LEN_MAX
            || $total <= 0
            || $ttl <= 0
        ) {
            return false;
        }

        return $this->guardian->set(
            $this->getSettingKey($key),
            $this->settingsToString($total, $ttl)
        );
    }

    /**
     * check guard can pass
     *
     * @param string $key guardian key
     * @return bool return false if current counts greater than $total
     */
    public function guard(string $key): bool
    {
        $setting = $this->getSettings($key);
        // $key doesn`t in guard list
        if (is_null($setting)) {
            return true;
        }

        $curVal = 0;
        $curTime = time();

        ($setting['expired'] >= $curTime)
            && ($curVal = $this->guardian->get($this->getRateKey($key)));
        if (empty($curVal)) {
            $setting['expired'] = $curTime + $setting['ttl'];
            $this->guardian->set(
                $this->getSettingKey($key),
                $this->settingsToString(
                    $setting['total'],
                    $setting['ttl'],
                    $setting['expired']
                )
            );
            $this->guardian->set($this->getRateKey($key), 1, $setting['ttl']);
            return true;
        }

        $this->guardian->set($this->getRateKey($key), ++$curVal, $setting['ttl']);

        return $setting['total'] >= $curVal;
    }

    /**
     * get settings and current counter in memory
     *
     * [
     *      'total' => @see guardianOn
     *      'ttl' => @see guardianOn
     *      'expired' => unix timestamp that calculating periods ended
     *      'current' => current counter value
     * ]
     *
     * @param string $key guardian key
     * @return array
     */
    public function show(string $key): ?array
    {
        $setting = $this->getSettings($key);
        // $key doesn`t in guard list
        if (is_null($setting)) {
            return null;
        }

        $setting['current'] = (int)$this->guardian->get($this->getRateKey($key));

        return $setting;
    }

    /**
     * stop guardian and clear cache
     *
     * @param string $key guardian key
     * @return void
     */
    public function guardianOff(string $key): void
    {
        if (empty($key)) {
            return;
        }

        $this->guardian->delete($this->getRateKey($key));
        $this->guardian->delete($this->getSettingKey($key));
    }

    private function getRateKey(string $key): string
    {
        return self::GUARDIAN_RATE . $key;
    }

    private function getSettingKey(string $key): string
    {
        return self::GUARDIAN_SETTING . $key;
    }

    private function settingsToString(int $total, int $ttl, int $expired = 0): string
    {
        return "{$total},{$ttl},{$expired}";
    }

    private function getSettings(string $key): ?array
    {
        $setting = $this->guardian->get($this->getSettingKey($key));
        // $key doesn`t in guard list
        if (empty($setting)) {
            return null;
        }

        $setting = explode(',', $setting);
        if (count($setting) != 3) {
            return null;
        }

        return [
            'total' => (int)$setting[0],
            'ttl' => (int)$setting[1],
            'expired' => (int)$setting[2],
        ];
    }
}
