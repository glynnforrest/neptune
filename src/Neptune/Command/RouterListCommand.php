<?php

namespace Neptune\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class RouterListCommand extends Command
{

    protected $name = 'router:list';
    protected $description = 'List all available routes';

    protected function configure()
    {
        $this->setName($this->name)
             ->setDescription($this->description)
             ->addArgument(
                 'module',
                 InputArgument::OPTIONAL,
                 'Only list routes from a given module'
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $module = $input->getArgument('module');

        $router = $this->neptune['router'];

        if ($module) {
            $router->routeModule($this->neptune->getModule($module), $this->neptune);
        } else {
            $router->routeModules($this->neptune);
        }
        $routes = array_map(function($route) {
            return [$route->getName(), $route->getUrl(), $route->getController(), $route->getAction()];
        }, $router->getRoutes());

        $table = $this->getHelper('table');
        $table->setHeaders(array('Name', 'Url', 'Controller', 'Action'))
            ->setRows($routes);
        $table->render($output);

        $output->writeln(sprintf('<info>%s</info> registered routes.', count($routes)));
    }

}
