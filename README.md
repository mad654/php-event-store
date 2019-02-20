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

Ist eine Liste von Events die sich auf das gleiche Subjekt beziehen und
damit seinen Lebenszyklus repräsentieren.