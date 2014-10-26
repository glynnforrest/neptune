<?php

namespace Neptune\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class RouterListCommand extends Command
{

    protected $name = 'router:list';
    protected $description = 'List all available routes';

    protected function configure()
    {
        $this->setName($this->name)
             ->setDescription($this->description)
             ->addOption(
                 'module',
                 'm',
                 InputOption::VALUE_REQUIRED,
                 'Only list routes from a given module'
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $module = $input->getOption('module');

        $router = $this->neptune['router'];

        if ($module) {
            $router->routeModule($this->neptune->getModule($module), $this->neptune->getRoutePrefix($module), $this->neptune);
        } else {
            $router->routeModules($this->neptune);
        }

        foreach ($router->getRoutes() as $route) {
            $output->writeln($route->getUrl());
        }
    }

}
