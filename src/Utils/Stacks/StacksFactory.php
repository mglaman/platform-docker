<?php

namespace mglaman\PlatformDocker\Utils\Stacks;
use mglaman\Toolstack\Stacks;
use mglaman\Toolstack\Toolstack;

class StacksFactory {

    public static function getStack($dir) {
        return Toolstack::inspect($dir);
    }

    public static function configure($stackType) {
        switch ($stackType) {
            case Stacks\Drupal::TYPE:
                $drupal = new Drupal();
                $drupal->configure();
                break;
            case Stacks\WordPress::TYPE:
                $wordpress = new WordPress();
                $wordpress->configure();
                break;
        }
    }
}
