<?php

namespace Neptune\Command;

use Neptune\Console\Console;
use Symfony\Component\Console\Input\InputOption;

class ServiceListCommand extends Command
{

    protected $name = 'service:list';
    protected $description = 'List all available services';

    protected function configure()
    {
        $this->setName($this->name)
             ->setDescription($this->description)
             ->addOption(
                 'names',
                 'N',
                 InputOption::VALUE_NONE,
                 'Only list names of services, do not resolve them from the container'
             );
    }

    public function go(Console $console)
    {
        $services = $this->neptune->keys();

        $console->writeln('<info>Available services</info>');

        sort($services);

        if ($this->input->getOption('names')) {
            foreach ($services as $service) {
                $console->writeln($service);
            }
        } else {
            foreach ($services as $service) {
                $console->writeln(sprintf('%s: %s', $service, $this->asString($this->neptune[$service])));
            }
        }
    }

    public function asString($object)
    {
        if (is_object($object)) {
            return get_class($object);
        }
        if (is_array($object)) {
            return sprintf('(array with %s items)', count($object));
        }

        return var_export($object, true);
    }

}
