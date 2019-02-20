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

EventEmitter sind Subjects die über EventStream persistiert werden.

Sobald ein EventEmitter dem ObjectEventStore mittels attach hinzugefügt
wurde, werden alle aktuellen und alle neuen Events persistiert.

Ob alle neuen Events persistiert werden, hängt von der Implementierung
des Subjects ab.