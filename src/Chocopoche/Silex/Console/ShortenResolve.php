<?php
namespace Chocopoche\Silex\Console;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShortenResolve extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure() {
        $this
            ->setName("shorten:resolve")
            ->setDescription("Resolves a short code.")
            ->addArgument(
                'short_code',
                InputArgument::REQUIRED,
                'The short code to resolve.'
            );

        $cmd = $this;
        $this->setCode(function (InputInterface $input, OutputInterface $output) use ($cmd) {
            $app        = $cmd->getApplication()->getSilexApplication();
            $short      = $app['short_url'];
            $short_code = $input->getArgument('short_code');
            $url        = $short->getByShortCode($short_code);


            if ($url) {
                $output->writeln($url['url']);
            }
            else {
                $output->writeln('No match found.');
            }
        });
    }
}
