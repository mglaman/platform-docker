#!/usr/bin/env php
<?php
/**
 * @file
 * Platform Docker Phar stub.
 */
if (class_exists('Phar')) {
    Phar::mapPhar('default.phar');
    require 'phar://' . __FILE__ . '/platform-docker';
}
__HALT_COMPILER(); ?>