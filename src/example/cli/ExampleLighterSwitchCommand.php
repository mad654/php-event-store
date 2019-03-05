<?php

namespace mad654\eventstore\example\cli;


use mad654\eventstore\EventSourcedObjectStore;
use mad654\eventstore\example\LightSwitch;
use mad654\eventstore\FileEventStream\FileEventStreamFactory;
use mad654\eventstore\StringSubjectId;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExampleLighterSwitchCommand extends Command
{
    protected function configure()
    {
        parent::configure();
        $this->addArgument("lighter-name", InputOption::VALUE_REQUIRED);
        $this->addOption("on", "1", InputOption::VALUE_NONE);
        $this->addOption("off", "0", InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('lighter-name');
        $on = $input->getOption('on');
        $off = $input->getOption('off');

        if (empty($name)) {
            throw new \InvalidArgumentException("lighter-name must not be empty");
        }

        if ($on === $off) {
            throw new \InvalidArgumentException("--on OR --off must be defined");
        }

        $factory = new FileEventStreamFactory(ExampleLighterInitCommand::STREAM_STORAGE_PATH);
        $store = new EventSourcedObjectStore($factory);
        $switch = $store->get(StringSubjectId::fromString($name));

        if ($switch instanceof LightSwitch) {
            if ($on) {
                $switch->switchOn();
                $output->writeln("Switched $name on");
                return;
            }

            if ($off) {
                $switch->switchOff();
                $output->writeln("Switched $name off");
                return;
            }
        }

        throw new \InvalidArgumentException("$name is not a LightSwitch");
    }

}