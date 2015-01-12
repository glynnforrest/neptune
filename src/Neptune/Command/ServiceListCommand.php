<?php

namespace Neptune\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Pimple\Container;

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
        $output->writeln('<info>Available services</info>');

        if ($input->getOption('names')) {
            foreach ($this->neptune->keys() as $service) {
                $output->writeln($service);
            }

            return;
        }

        $this->printTree($output, $this->buildTree($this->neptune));
    }

    protected function printTree(OutputInterface $output, array $tree, $depth = 0)
    {
        foreach ($tree as $service => $description) {
            $depth_prefix = str_repeat('    ', $depth);

            if (is_array($description)) {
                $output->writeln($depth_prefix.$service.': Pimple\Container');
                $this->printTree($output, $description, $depth + 1);
                continue;
            }

            $output->writeln($depth_prefix.$service.': '.$description);
        }
    }

    protected function buildTree(Container $container)
    {
        $services = $container->keys();
        sort($services);
        $tree = [];

        foreach ($services as $service) {
            try {
                $tree[$service] = $this->describeObject($container[$service]);
            } catch (\Exception $e) {
                throw new \Exception(sprintf('An exception occurred resolving "%s": %s', $service, $e->getMessage()));
            }
        }

        return $tree;
    }

    protected function describeObject($object)
    {
        if (is_object($object)) {
            if ($object instanceof Container) {
                return $this->buildTree($object);
            }

            return get_class($object);
        }

        if (is_array($object)) {
            return sprintf('(array with %s items)', count($object));
        }

        return var_export($object, true);
    }
}
