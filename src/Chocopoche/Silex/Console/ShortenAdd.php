<?php
namespace Chocopoche\Silex\Console;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShortenAdd extends BaseCommand
{

    /**
     * {@inheritDoc}
     */
    protected function configure() {
        $this
            ->setName("shorten:add")
            ->setDescription("Resolves a short code.")
            ->addArgument(
                'url',
                InputArgument::REQUIRED,
                'The url to shorten.'
            );

        $cmd = $this;
        $this->setCode(function (InputInterface $input, OutputInterface $output) use ($cmd) {
            $app = $cmd->getApplication()->getSilexApplication();
            $url = $input->getArgument('url');

            // TODO: use the existing form to validate
            if(filter_var($url, FILTER_VALIDATE_URL)){ 
                $id = $app['short_url']->add($url);
                $url_details = $app['short_url']->getById($id);
                $output->writeln($url_details['short_code'] . '|' . $url_details['url']);
            } 
            else {
                $output->writeln($url);
                $output->writeln('This value is not a valid URL.');
            }

        });
    }
}
