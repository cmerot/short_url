<?php
namespace Chocopoche\Silex\Console;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DBCreateDatabase extends BaseCommand
{

    protected function configure() {
        $this
            ->setName("db:create")
            ->setDescription("Sqlite only! Creates the database and import the schema.");

        $cmd = $this;
        $this->setCode(function (InputInterface $input, OutputInterface $output) use ($cmd) {
            $app    = $cmd->getApplication()->getSilexApplication();
            $conn   = $app['db'];
            $params = $conn->getParams();

            if ($params['driver'] == 'pdo_sqlite') {
                $dbname = $params['path'];
            } else {
                $dbname = $params['dbname'];
            }

            $sm = new \Doctrine\DBAL\Schema\Schema;
            $conn->getSchemaManager()->createDatabase($dbname);
            $conn_params = $conn->getParams();
            if ($conn_params['path'] && ! is_file($conn_params['path'])) {
                $conn->getSchemaManager()->createDatabase($conn_params['path']);
                $output->writeln('Database created and schema imported.');
            } else {
                $output->writeln('Database already exists. Aborting.');
            }
        });
    }
}
