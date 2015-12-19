<?php

namespace Neptune\Command\ActiveDoctrine;

use ActiveDoctrine\Schema\SchemaCreator;
use Neptune\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Update the database to the current entity schema.
 *
 * @author Glynn Forrest <me@glynnforrest.com>
 **/
class SchemaUpdateCommand extends Command
{
    protected $name = 'active-doctrine:schema:update';
    protected $description = 'Update the schema of the database';

    protected function configure()
    {
        parent::configure();
        $this->addOption(
            'confirm',
            '',
            InputOption::VALUE_NONE,
            'Actually execute the SQL statements'
        )->addOption(
            'show-sql',
            '',
            InputOption::VALUE_NONE,
            'Print the SQL statements'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schema_creator = new SchemaCreator();
        foreach ($this->neptune->getModules() as $module) {
            $schema_creator->addEntityDirectory($module->getNamespace().'\\Entity', $module->getDirectory().'Entity');
        }

        $schema = $schema_creator->createSchema();
        $conn = $this->neptune['db'];
        $existing_schema = $conn->getSchemaManager()->createSchema();

        $queries = $existing_schema->getMigrateToSQL($schema, $conn->getDatabasePlatform());

        if (empty($queries)) {
            $output->writeln('Database schema already up to date.');

            return;
        }

        if (!$input->getOption('confirm') && !$input->getOption('show-sql')) {
            $output->writeln(sprintf('<info>%s</info> %s to execute.', count($queries), count($queries) > 1 ? 'queries' : 'query'));
            $output->writeln('<info>'.$this->name.' --confirm</info> to run the queries');
            $output->writeln('<info>'.$this->name.' --show-sql</info> to show the queries');
            $output->writeln('<info>'.$this->name.' --confirm --show-sql</info> to do both');

            return;
        }

        foreach ($queries as $query) {
            if ($input->getOption('confirm')) {
                $conn->executeQuery($query);
            }
            if ($input->getOption('show-sql')) {
                $output->writeln($query);
            }
        }
        if ($input->getOption('confirm')) {
            $output->writeln(sprintf('Executed <info>%s</info> %s.', count($queries), count($queries) > 1 ? 'queries' : 'query'));
        }
    }
}
