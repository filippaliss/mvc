<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * @property array<string, mixed> $instanceof
 * @property string $name
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;
}
