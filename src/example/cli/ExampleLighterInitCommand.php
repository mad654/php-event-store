<?php

namespace mad654\eventstore\example\cli;


use mad654\eventstore\EventSourcedObjectStore;
use mad654\eventstore\example\LightSwitch;
use mad654\eventstore\FileEventStream\FileEventStreamFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExampleLighterInitCommand extends Command
{
    const STREAM_STORAGE_PATH = "/tmp/var/eventstore-example";

    protected function configure()
    {
        parent::configure();
        $this->addArgument("lighter-name", InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('lighter-name');

        if (empty($name)) {
            throw new \InvalidArgumentException("lighter-name must not be empty");
        }

        $factory = new FileEventStreamFactory(self::STREAM_STORAGE_PATH);
        $store = new EventSourcedObjectStore($factory);

        $switch = new LightSwitch($name);
        $store->attach($switch);

        $output->writeln("$name switch initialised");
    }

}