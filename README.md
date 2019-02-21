# event store php implementation

## os dependencies

- composer in path
- watchexec in path

In local_develop is a prepared Vagrantfile
which installs all needed dependencies.

## getting started

```
composer install
make test
```

## development

```
make test.watch
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

## motivation

object relational mapping is hard, even its welcovered topic, you can only achive about 80% cases working: which?

ORM needs a lot of work around ( db schema, migrations, db administration for different stages, db data migration between stages ( live -> staging -> local_develop )

persistence easy like working with objects

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
    private $kitchen = 'off';
    
    public function isKitchenOn(): bool {
        return $this->kitchen;
    }
    
	public function switchKitchenOn()
    {
        if ($this->kitchen === 'on') return;
        // do some stuff which does the hard work
        $this->kitchen = 'on';
    }
    
    public function switchKitchenOff()
    {
        if ($this->kitchen === 'off') return;
         // do some stuff which does the hard work
        $this->kitchen = 'off';
    }
}
```

This is a good beginning, but now you need a way to persist the state.

### EventStreamEmitter

Instead of creating a database you can extend your class to implement the EventStreamEmitter interface. An EventStreamEmitter is simply an object which should be available in his current state in the next request and for this it can publish its events as a stream and can be build from scratch based on the events:

```php
<?php

namespace mad654\eventstore\example;


use mad654\eventstore\Event;
use mad654\eventstore\event\StateChanged;
use mad654\eventstore\EventStream\AutoTrackingEventStreamEmitter;
use mad654\eventstore\EventStream\EventStreamEmitter;
use mad654\eventstore\MemoryEventStream\MemoryEventStream;

class LightSwitch implements EventStreamEmitter
{
    use AutoTrackingEventStreamEmitterTrait;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $kitchen;

    public function __construct(string $id)
    {
        $this->events = new MemoryEventStream();
        $this->record(new StateChanged(['id' => $id, 'kitchen' => false]));
        $this->constructorInvocationCount++;
    }

    public function subjectId(): string
    {
        return $this->id;
    }

    public function isKitchenOn(): bool
    {
        return $this->kitchen;
    }

    public function switchKitchenOn()
    {
        if ($this->kitchen) return;
        $event = new StateChanged(['kitchen' => true]);
        $this->record($event);
    }

    public function switchKitchenOff()
    {
        if (!$this->kitchen) return;
        $event = new StateChanged(['kitchen' => false]);
        $this->record($event);
    }

    private function on(Event $event)
    {
        $this->id = $event->get('id', $this->id);
        $this->kitchen = $event->get('kitchen', $this->kitchen);
    }

}
```

So instead of changing your member variables directly, you will use events for this, like shown in `switchKitchenOn`



### last but not least

```php
class Lighter {	
    use ImutableDtoTrait;
    
    private $id;
    private $light;
    
    public function __construct(string $id) {
        // EventBasedState::record will change its properties (on) + appends evt to stream
        $this->record(new GenericEvent(['id' => $id, 'light' => 'off'));
        $this->enableEventSourcing(['id', 'light']);
    }
    
	public function switchLightOn()
    {
        if ($this->light === 'on') return;
        // do some stuff which does the hard work
        $this->record(new GenericEvent(['light' => 'on'));
    }
    
    public function switchLightOff()
    {
        if ($this->light === 'off') return;
         // do some stuff which does the hard work
        $this->record(new GenericEvent(['light' => 'off'));
    }
                                        
    public function willFail() {
        $this->light = 'off'; // throws ImutablePropertyException
    }
}
```



### With transpiler this may be possible - will this work on child objects?

```php
evtsourced class Lighter {	
    evtsourced private $id;
    evtsourced private $light;
    
    evtsourced public function __construct(string $id) {
        // EventBasedState::record will change its properties (on) + appends evt to stream
        $this->id = $id;
        $this->light = $light;
        // at the end of each public function, all property changes are recorded as a event,
        // with the function name as indent
    }
    
	evtsourced public function switchLightOn()
    {
        if ($this->light === 'on') return;
        // do some stuff which does the hard work
        $this->light = 'off'
    }
    
    evtsourced public function switchLightOff()
    {
        if ($this->light === 'off') return;
         // do some stuff which does the hard work
        $this->light = 'on'
    }
}
```



### EventObjectStore

```php
use mad654\eventstore\FileEventStream\FileEventStreamFactory;
use mad654\eventstore\EventObjectStore;

$factory = new FileEventStreamFactory("/tmp/eventstore-example");
$store = new EventObjectStore($factory);
```

The EventObjectStore provides a simple API which allows you to save and load objects which implements the EventEmitter interface:

```php
$store->attach($someEventEmitter);
unset($someEventEmitter);
$someEventEmitter = $store->get('id-of-some-event-emitter');
```

If '$someEventEmitter' was implemented correctly, it should have the equal state - before and after the `unset()` call.

By definition an EventObjectStore can only store and retrieve objects by id. Here you can find solutions for searching ...

### Event

immutable by definition

### Putting all pices together

```php
use mad654\eventstore\FileEventStream\FileEventStreamFactory;
use mad654\eventstore\EventObjectStore;

$factory = new FileEventStreamFactory("/tmp/eventstore-example");
$store = new EventObjectStore($factory);

$lighter = new Lighter('kitchen');
$store->attach($lighter);
```



Some times later in an other request you want to switch on the light in the kitchen:

``` php
$store->get('kitchen')->switchLightOn();
```

And again later you will switch it off again:

```php
$store->get('kitchen')->switchLightOff();
```



And again ...

And again ...

And now you are wondering why you power bill is so expensive - let's take a look at the history:

```php
$formatter = new CliEventStreamFormatter();
echo $store->get('kitchen')->history($formatter);

# timestamp           | event_type   | property | new_state
# 2018-12-01 18:10:00 | GenericEvent | Lighter  | on
# 2018-12-01 18:12:00 | GenericEvent | Lighter  | off
# 2018-12-01 19:30:00 | GenericEvent | Lighter  | on
# 2018-12-03 18:10:00 | GenericEvent | Lighter  | off
```

