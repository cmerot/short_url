<?php
namespace Chocopoche\Silex\Console;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI command to create a database via `db:create`.
 */
class DBCreateDatabase extends BaseCommand
{

    /**
     * The command itself.
     */
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
                if (! is_file($dbname)) {
                    $old_umask = umask(0);
                    if (!is_dir(dirname($dbname))) {
                        mkdir(dirname($dbname), 0777, true);
                    }
                    $sm = new \Doctrine\DBAL\Schema\Schema;
                    $conn->getSchemaManager()->createDatabase($dbname);
                    chmod($dbname, 0666);
                    umask($old_umask);
                    $output->writeln('Database created (world writable).');
                }
                else {
                    $output->writeln('Database already exists. Aborting.');
                }
            }
            else {
                $output->writeln('Supports only sqlite. Aborting.');
            }
        });
    }
}
