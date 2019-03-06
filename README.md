# event store php implementation

## os dependencies

- composer in path

In local_develop is a prepared Vagrantfile
which installs all needed dependencies.

## getting started

```bash
cd local_develop
vagrant up
vagrant ssh
```

Inside of the vagrant box:

```bash
composer install
make test
```

### example

For a full working example take a look at `src/example`. In your vagrant box you can use it like this:

```bash
# create storage folder
mkdir -p /tmp/var/eventstore-example/ 

# create a new instance of LightSwitch with id 'kitchen'
src/example/bin/console.php init kitchen

# switch on/off on 'kitchen'
src/example/bin/console.php switch kitchen --on
src/example/bin/console.php switch kitchen --off

# render history of 'kitchen'
src/example/bin/console.php history kitchen
```

## motivation

object relational mapping is hard, even if its well covered topic, you can only achive about 80% cases working: which?

ORM needs a lot of work around ( db schema, migrations, db administration for different stages, db data migration between stages ( live -> staging -> local_develop )

persistence easy like working with objects

### How to solve

track all state changes instead of last state, which give this opportunities (based on event sourcing):

- express intention
- more informations
- git like merging if your objects shared over multiple systems which maybe offline some time but could change in this time
- easy setup - just plain php and filesystem
- just write business logic instead of db boilerplate stuff + less code

think objects of your domain as process which different states, each state change is represented as a event.

## usage

Lets take this little example to get in touch with all the new stuff. Lets asume you want to control the light in your Kittchen and for this you have build some switch. All you need is an object which can control the state and keeps its current state over mutlitple requests:

```php
class LightSwitch {	
    private $state = false;
    
    public function isOn(): bool {
        return $this->state;
    }
    
    public function switchOn()
    {
        if ($this->state === true) return;
        // do some stuff which does the hard work
        $this->state = true;
    }
    
    public function switchOff()
    {
        if ($this->state === false) return;
         // do some stuff which does the hard work
        $this->state = false;
    }
}
```

This is a good beginning, but now you need a way to persist the state.

### EventSourceObject

Instead of creating a database you can extend your class to implement the EventSourcedObject interface. An EventSourcedObject is simply an object which should be available in his current state in the next request and for this it can publish its events as a stream and can be build from scratch based on the events:

```php
<?php
use mad654\eventstore\Event;
use mad654\eventstore\Event\StateChanged;
use mad654\eventstore\EventSourcedObject;
use mad654\eventstore\EventStream\AutoTrackingEventSourcedObjectTrait;
use mad654\eventstore\SubjectId;

class LightSwitch implements EventSourcedObject
{
    use AutoTrackingEventSourcedObjectTrait;

    /**
     * @var bool
     */
    private $state;

    public function __construct(SubjectId $id)
    {
        $this->init($id, ['state' => false]);
    }

    public function isOn(): bool
    {
        return $this->state;
    }

    public function switchOn()
    {
        if ($this->state) return;
        $this->record(new StateChanged($this->subjectId(), ['state' => true]));
    }

    public function switchOff()
    {
        if (!$this->state) return;
        $this->record(new StateChanged($this->subjectId(), ['state' => false]));
    }

    public function on(Event $event): void
    {
        $this->state = $event->get('state', $this->state);
    }

}
```

So instead of changing your member variables directly, you will use events for this, like shown in `switchOn`. So you will `record` an event and you will update your state in the `on` function, which is automatically be called by `record` function.

If you want to see more details, take a look at the `AutoTrackingEventSourcedObjectTrait` which should be a good starting point for all your event sourced objects and reduce boilerplate code. 


### EventSourcedObjectStore

```php
use mad654\eventstore\FileEventStream\FileEventStreamFactory;
use mad654\eventstore\EventObjectStore;

$factory = new FileEventStreamFactory("/tmp/eventstore-example");
$store = new EventSourcedObjectStore($factory);
```

The EventSourcedObjectStore provides a simple API which allows you to save and load objects which implements the EventEmitter interface:

```php
$store->attach($someEventSourcedObject);
unset($someEventSourcedObject);
$id = StringSubjectId::fromString('id-of-some-object');
$someEventEmitter = $store->get($id);
```

If '$someEventSourcedObject' was implemented correctly, it should have the equal state - before and after the `unset()` call.

By definition an EventSourcedObjectStore can only store and retrieve objects by id. Here you can find solutions for searching ...

### Event

In this example we use the StateChanged event, in production you should
create subclasses of this to express more precisely what happened.

In general events are immutable.

### Putting all pices together

```php
use mad654\eventstore\FileEventStream\FileEventStreamFactory;
use mad654\eventstore\EventObjectStore;

$factory = new FileEventStreamFactory("/tmp/eventstore-example");
$store = new EventSourcedObjectStore($factory);

$switch = new LightSwitch('kitchen');
$store->attach($switch);
```



Some times later in an other request you want to switch on the light in the kitchen:

``` php
$store->get(StringSubjectId::fromString('kitchen'))->switchOn();
```

And again later you will switch it off again:

```php
$store->get(StringSubjectId::fromString('kitchen'))->switchOff();
```



And again ...

And again ...

And now you are wondering why your power bill is so expensive - let's take a look at the history:

```php
$renderer = new ArrayEventStreamRenderer();
$store->get(StringSubjectId::fromString('kitchen'))->history($renderer);
$data = $renderer->toArray();

// use symonfy command style to render a nice table on command line
$io = new SymfonyStyle($input, $output);
$io->table(
    ['nr', 'timestamp', 'event_type', 'id', 'property', 'new_state'],
    $data
);

# nr | timestamp           | event_type   | id       | property | new_state
# 1  | 2018-12-01 18:10:00 | StateChanged | kittchen | state    | on
# 2  | 2018-12-01 18:12:00 | StateChanged | kittchen | state    | off
# 3  | 2018-12-01 19:30:00 | StateChanged | kittchen | state    | on
# 4  | 2018-12-03 18:10:00 | StateChanged | kittchen | state    | off
```

## concepts

### EventStream

Ist eine Liste von Events.

EventEmitter sind Objekte die über EventStream persistiert werden.

Sobald ein EventEmitter dem ObjectEventStore mittels attach hinzugefügt
wurde, werden alle aktuellen und alle neuen Events persistiert.

Ob alle neuen Events persistiert werden, hängt von der Implementierung
des Subjects ab.

@TBD API die state changes über events super einfach macht

### EventConsumer / Projector

@TBD

## development

```
watch make test.unit
```

## 