<?php

namespace mad654\eventstore\example\cli;


use mad654\eventstore\Event;
use mad654\eventstore\EventSourcedObjectStore;
use mad654\eventstore\EventStream\EventStream;
use mad654\eventstore\EventStream\EventStreamRenderer;
use mad654\eventstore\FileEventStream\FileEventStreamFactory;
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

    public function render(EventStream $events): void
    {
        $this->history = [['nr', 'timestamp', 'event_type', 'property', 'new_state']];

        // FIXME: create IntermediateStateProjector
        $projector = new IntermediateStateProjector();
        $projector->replay($events);
        foreach ($projector->getIterator() as $state) {
            $new = [
                'nr' => $counter++,
                'timestamp' => $state['last_event']['timestamp'],
                'type' => $state['last_event']['type']
            ];

            foreach ($state['properties'] as $key => $value) {
                $new[$key] = $value;
            }

            $this->history[] = $new;
        };

        // FIXME: flatten properties of
        // ['id' => '...', 'timestamp' => ' ... ', 'type' => ' ... ', 'properties' => [ ... ]]
        foreach ($events as $event) {
            $this->renderEvent($event);
        }
    }

    private function renderEvent(Event $event): void
    {
        try {
            $type = (new \ReflectionClass($event))->getShortName();
        } catch (\ReflectionException $reflectionException) {
            $type = 'UNKNOWN';
        }

        $value = $event->get('state', null);
        if ($value === true) $value = 'on';
        if ($value === false) $value = 'off';

        $this->history[] = [
            count($this->history),
            $event->timestamp()->format(DATE_ATOM),
            $type,
            'state',
            $value,
        ];
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