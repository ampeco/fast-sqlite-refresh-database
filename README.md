Alternative to the default laravel behaviour that will refresh the sqlite database for each test.

Also, an alternative to squashing migrations, since that still has to import the squashed migration on each test.

In this implementation no transactions are used when starting a test and the database is refreshed by deleting the sqlite file and recreating it from a clean template!


# Instructions

1. Make sure you are not using in-memory database, but a sqlite file based one for your test setup
2. In your test instead of `use RefreshDatabase;` use `use FastSqliteRefreshDatabase;`
3. Run your tests

# Performance

We tested it in an internal Feature testing suit with 86 tests. Using `LazyRefreshDatabase` we got:
```
ParaTest v6.9.1 upon PHPUnit 9.6.7 by Sebastian Bergmann and contributors.

................................................................. 65 / 86 ( 75%)
.....................                                             86 / 86 (100%)

Time: 01:09.101, Memory: 39.12 MB
```

Then we switched to `FastSqliteRefreshDatabase` and got:
```
ParaTest v6.9.1 upon PHPUnit 9.6.7 by Sebastian Bergmann and contributors.

................................................................. 65 / 86 ( 75%)
.....................                                             86 / 86 (100%)

Time: 00:06.894, Memory: 39.12 MB
```