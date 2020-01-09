# migrations

Сreating migrations for a project

## Quickstart

commands:

```sh
$ mkdir migrations

$ composer require devsuperstars/migrations
```

 Create your migrations in the directory  migrations/ as sql files in order, starting with the number 1.

Example: migrations/1.sql, migrations/2.sql ... migrations/n.sql


run migrations:

```sh
$ composer migrate
```
