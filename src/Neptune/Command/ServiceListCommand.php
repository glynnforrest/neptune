<?php

namespace Neptune\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $services = $this->neptune->keys();

        $output->writeln('<info>Available services</info>');

        sort($services);

        if ($input->getOption('names')) {
            foreach ($services as $service) {
                $output->writeln($service);
            }
        } else {
            foreach ($services as $service) {
                try {
                    $output->writeln(sprintf('%s: %s', $service, $this->asString($this->neptune[$service])));
                } catch (\Exception $e) {
                    throw new \Exception(sprintf('An exception occurred resolving "%s": %s', $service, $e->getMessage()));
                }
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
