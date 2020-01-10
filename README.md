
# migrations

Сreating migrations for a project

## migrations can only work with the lipid framework

## Quickstart



```sh
$ mkdir migrations

$ composer require devsuperstars/migrations
```

Create your migrations in the directory  migrations/ as sql files in order, starting with the number 1.

Example: migrations/1.sql, migrations/2.sql ... migrations/n.sql

SQL file must contain sql-query, for example 
```sh
ALTER TABLE `db`.`table`
ADD COLUMN `id` INT(11) NOT NULL AUTO_INCREMENT FIRST,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`);
}

```
SQL file can contain multiple sql queries


add to your composer.json scripts

```sh
"scripts": {
...
 "migrate": [
      "vendor/bin/migrate"
    ]
}

```

run migrations:

```sh
$ composer migrate
```
