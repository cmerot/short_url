<?php
namespace Chocopoche\Silex\Console;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
class DBImportSchema extends BaseCommand
{

    protected function configure() {
        $this
            ->setName("db:import-schema")
            ->setDescription("Imports the schema into the database.");

        $cmd = $this;
        $this->setCode(function (InputInterface $input, OutputInterface $output) use ($cmd) {
            $app  = $cmd->getApplication()->getSilexApplication();
            $conn = $app['db'];

            $app['short_url']->importSchema();
            $output->writeln('Schema imported.');
        });
    }
}
