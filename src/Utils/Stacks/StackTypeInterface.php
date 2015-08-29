<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/28/15
 * Time: 2:55 PM
 */

namespace mglaman\PlatformDocker\Utils\Stacks;


interface StackTypeInterface
{
    public function configure();
    public function dbFromDocker();
    public function dbFromLocal();
}
