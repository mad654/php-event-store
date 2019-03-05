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

    /**
     * @var StateProjector
     */
    private $projector;

    /*
     * TODO: change example to 2 properties?

     * TODO: Limit print to last 3 Events
     *
     * FIXME: implement like this + make this default without counter
     * $this->projector->on($event);
     * $this->history[] = $this->projector->getIterator($map = function(Event $event, $data) {
     *      $entry = [
     *          count($this->history);
     *          $event->timestamp()->format(DATE_ATOM),
     *          $data['__meta']['type'],
     *          $data['__meta']['id'],
     *      ];
     *      unset($data['_meta'];
     *      return array_merge($entry, $data);
     * });
    try {
        $type = (new \ReflectionClass($event))->getShortName();
    } catch (\ReflectionException $reflectionException) {
        $type = 'UNKNOWN';
    }
    */

    public function render(EventStream $events): void
    {
        // TODO: Print Line per Property instead of per Event to keep max 80 width
        // TODO: Add table separator between events

        $this->history = [['nr', 'timestamp', 'event_type', 'id', 'property', 'new_state']];
        $this->projector = new StateProjector();
        $this->projector->replay($events);
        // FIXME: returns StateProjector as $data, $data->timestamp(),
        // FIXME: returns StateProjector as $data, $data->type(),
        // FIXME: returns StateProjector as $data, $data->subjectId(),
        // FIXME: returns StateProjector as $data, $data->subjectType(),
        // FIXME: returns StateProjector as $data, $data->state(),
        foreach (StateProjector::intermediateIterator($events) as $data) {
            $entry = [
                count($this->history),
                $data['__meta']['timestamp'],
                $data['__meta']['type'],
                $data['__meta']['subject']['id'],
            ];

            unset($data['__meta']);
            foreach ($data as $key => $value) {
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