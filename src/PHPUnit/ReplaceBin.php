<?php

namespace PHPUnit;

use Composer\Util\Filesystem;

class ReplaceBin
{

    public static function copy($event)
    {
        $composer = $event->getComposer();
        $config = $composer->getConfig();
        $filesystem = new Filesystem();
        $vendorPath = $filesystem->normalizePath(realpath($config->get('vendor-dir')));
        $targetDir = $vendorPath . '/bin';
        $filesystem->ensureDirectoryExists($targetDir);
        $filesystem->copy($vendorPath . "/rock/phpunit/bin/phpunit", $targetDir . '/phpunit');
        chmod($targetDir . '/phpunit', 0755);
    }
}

