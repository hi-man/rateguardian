# Rate Guardian

[![Build Status](https://secure.travis-ci.org/hi-man/rateguardian.png)](https://travis-ci.org/hi-man/rateguardian)

> keep your server calm down

# Requirements

- php 7.3+
- yac 2.0+

# Installation

```
composer require hi-man/rateguardian
```

# Usage

## step 1 : initialize guardian

```
RateGuardian::getInstance()->guardianOn($key, $total, $ttl)
```

- `$key` the unique guardian key, such as api pathname, not longer than 41 characters
- `$ttl` in seconds, a counter will be increased in this period
- `$total` the max counter value allowd in `$ttl` second

## step 2 : guard api with guardian key

```
RateGuardian::getInstance()->guard($key);
```

- a `false` value indicates the api is overloaded, application should handle this situation instead of providing service
- return value
  - return `true` if counter less than `$total`
  - return `true` if counter equals `$total`
  - return `true` if `$key` does not be registered with `guardianOn`
  - otherwise return `false`

## step 3 : clear guardian

```
RateGuardian::getInstance()->guardianOff($key);
```

- restart `php-fpm` or php script also cleared guardian setting

## optional: get guardian info

```
RateGuardian::getInstance()->show($key);
```

- return a array of guardian info
  - `total` the value provided by `guardianOn`
  - `ttl` the value provided by `guardianOn`
  - `expired` unix timestamp that calculating periods ended
  - `current` current counter value
