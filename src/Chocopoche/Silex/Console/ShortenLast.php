<?php
namespace Chocopoche\Silex\Console;
use \Symfony\Component\Console\Command\Command as BaseCommand;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

class ShortenLast    extends BaseCommand
{

    /**
     * {@inheritDoc}
     */
    protected function configure() {
        $this
            ->setName("shorten:last")
            ->setDescription("Shortens the given URL.");

        $cmd = $this;
        $this->setCode(function (InputInterface $input, OutputInterface $output) use ($cmd) {
            $app   = $cmd->getApplication()->getSilexApplication();
            $short = $app['short_url'];
            $last  = $short->getLastShorten(10);

            foreach ($last as $url) {
                $output->writeln($url['short_code'] . '|' . $url['url']);
            }
        });
    }
}
