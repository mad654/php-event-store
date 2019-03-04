<?php

namespace mad654\eventstore\example\cli;


use mad654\eventstore\Event;
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

    /**
     * @var StateProjector
     */
    private $projector;

    public function render(EventStream $events): void
    {
        $this->history = [['nr', 'timestamp', 'event_type', 'id', 'property', 'new_state']];

        foreach ($events as $event) {
            $this->renderEvent($event);
        }
    }

    private function renderEvent(Event $event): void
    {
        if (is_null($this->projector)) {
            $this->projector = new StateProjector();
        }

        /*
         * TODO: Limit print to last 3 Events
         *
         * FIXME: refactor to projector as $data['__meta']['timestamp']
         * FIXME: refactor to projector as $data['__meta']['event_type']
         * FIXME: refactor to projector as $data['__meta']['id']
         * FIXME: refactor to projector as $data['__meta']['object_class']
        try {
            $type = (new \ReflectionClass($event))->getShortName();
        } catch (\ReflectionException $reflectionException) {
            $type = 'UNKNOWN';
        }
        */

        $this->projector->on($event);
        $data = $this->projector->toArray();

        $entry = [
            count($this->history),
            $event->timestamp()->format(DATE_ATOM),
            $data['__meta']['type'],
            $data['__meta']['id']
        ];

        $id = null;

        foreach ($data as $key => $value) {
            if ($key == 'class_name') continue;
            if ($key == 'id') {
                $id = $value;
                continue;
            }

            $value = ($value === true) ? 'on' : $value;
            $value = ($value === false) ? 'off' : $value;
            $entry[] = "$id";
            $entry[] = "$key";
            $entry[] = $value;
        }

        $this->history[] = $entry;
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