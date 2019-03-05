<?php

namespace mad654\eventstore\example\cli;


use mad654\eventstore\EventSourcedObjectStore;
use mad654\eventstore\EventStream\ArrayEventStreamRenderer;
use mad654\eventstore\FileEventStream\FileEventStreamFactory;
use mad654\eventstore\StringSubjectId;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExampleLighterHistoryCommand extends Command
{
    protected function configure()
    {
        parent::configure();
        $this->addArgument("lighter-name", InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('lighter-name');

        if (empty($name)) {
            throw new \InvalidArgumentException("lighter-name must not be empty");
        }

        $factory = new FileEventStreamFactory(ExampleLighterInitCommand::STREAM_STORAGE_PATH);
        $store = new EventSourcedObjectStore($factory);
        $switch = $store->get(StringSubjectId::fromString($name));

        $renderer = new ArrayEventStreamRenderer();
        $switch->history($renderer);
        $io->table(
            ['nr', 'timestamp', 'event_type', 'id', 'property', 'new_state'],
            $renderer->toArray()
        );
    }
}