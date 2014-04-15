<?php

namespace Neptune\Command;

use Neptune\Console\Console;

class CacheFlushCommand extends Command
{
    protected $name = 'cache:flush';
    protected $description = 'Empty the cache';

    public function go(Console $console)
    {
        $cache = $this->neptune['cache'];
        $console->verbose("Using cache driver <info>" . get_class($cache) . "</info>");
        $cache->flushAll();
        $console->writeln('Emptied the cache.');
    }

}
