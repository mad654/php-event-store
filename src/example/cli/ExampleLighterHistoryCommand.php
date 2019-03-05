<?php

namespace mad654\eventstore\example\cli;


use mad654\eventstore\EventSourcedObjectStore;
use mad654\eventstore\EventStream\EventStream;
use mad654\eventstore\EventStream\EventStreamRenderer;
use mad654\eventstore\FileEventStream\FileEventStreamFactory;
use mad654\eventstore\StateProjector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExampleLighterHistoryCommand extends Command implements EventStreamRenderer
{
    /**
     * @var array
     */
    private $history;

    /*
     * TODO: change example to 2 properties?

     * TODO: Limit print to last 3 Events
     *
    */

    public function render(EventStream $events): void
    {
        // TODO: Print Line per Property instead of per Event to keep max 80 width
        // TODO: Add table separator between events

        $this->history = [['nr', 'timestamp', 'event_type', 'id', 'property', 'new_state']];

        /* @var \mad654\eventstore\StateProjector $state */
        foreach (StateProjector::intermediateIterator($events) as $state) {
            $entry = [
                count($this->history),
                $state->lastEventTimestamp()->format(DATE_ATOM),
                $state->lastEventType(),
                $state->subjectId(),
            ];

            foreach ($state->projection() as $key => $value) {
                $entry[] = $key;
                $entry[] = $value;
            }

            $this->history[] = $entry;
        }
    }

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

        $factory = new FileEventStreamFactory(ExampleLighterInitCommand::STREAM_STORAGE_PATH);
        $store = new EventSourcedObjectStore($factory);
        $switch = $store->get($name);

        $this->history = null;
        $switch->history($this);
        $this->print(new SymfonyStyle($input, $output));

        throw new \RuntimeException("history $name needs refactoring");
    }


    private function print(SymfonyStyle $io): void
    {
        $io->table(
            array_shift($this->history),
            $this->history
        );
    }
}