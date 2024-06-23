# Requirement

- PHP 8.3
- Composer
- PostgreSQL 15 (alternatively SQLite or MariaDB would also works since there's no vendor-specific features used yet)
- symfony binary (optional)

# Setup

- clone the repo
- install all dependency, including dev dependency
- edit `.env`, adjust `DATABASE_URL`
- if database not created yet, run `php bin\console doctrine:database:create` (reqires superuser role in DSN)
- run `php bin\console doctrine:schema:create` and `php bin\console doctrine:migrations:migrate` to create the db
  structure
- run `php bin\console doctrine:fixtures:load` to prefill db with sample data.
- run `composer php-dev-server` (or `composer sf-dev-server` if symfony binary is installed)

# Testing

Either run `composer test` or `composer paratest`. The paratest script make use of paratest to run test in parallel, but
forego deprecation checking provided by simple-phpunit wrapper.

# Design Decisions

## Language and Library Choice

- PHP was picked because time constrains and only two options provided (PHP/ Python). Given some freedom I likely pick
  golang due to ease of development and tooling.
- Symfony was picked due to its stable nature and clear roadmap of releases.
  It also provides some helpers to ensure that future updates can be done relatively painlessly when upstream deprecates
  and/ or replace a feature that we use.
- PostgreSQL was picked because it is the most comfortable to work with compared to other alternatives.

## Schema Design

- Alteration was made in author-book relationship.
  Mainly due to mistakes made in reading the spec and assuming real-world scenario (where book and author is n:n
  relationship rather than n:1)
- No extra indexing was made at the moment (see improvement plan)
- No extra tuning was deemed necessary due to the nature of the data (see improvement plan)

## Testing

- Since the flow is straightforward CRUD, and Symfony already provide deprecation checker via phpunit-bridge, no unit
  test are needed.
- Integration test for controller are available for some (unable to cover all due to time constraint).

## Improvement Plan

- With more complicated data structure, we can add unit test on DTO to make sure validation worked.
- Add index on book.publish_date for possible filtering.
- Assuming the library API is used for common lookup, the read will skew towards recent books and/ or popular authors,
  the built-in caching mechanism can handle this kind of load. So it's possible that we can get away with only
  increasing
  db query cache.
- Add GIN index on following fields:

    - author.name
    - book.title
    - book.description

  Combined with tsvector query to allow fulltext search of partial match with better search result and query efficiency
  compared
  to LIKE clause.
  _\*Note that due to this being vendor-specific, it wasn't implemented at the moment_.
- No extra caching mechanism is used because there's no information about usage pattern available. We can improve it in
  multiple ways depends on db load:

    - If previous load assumption was correct (skew toward recent data). We can reduce the table and index size by
      partitioning
      the book data vertically based on publish_date.
    - After that, depends on budget and manpower we can either

        - scale horizontally with shards
        - use replication with write-master and multiple read-only slaves
        - scale up the hardware
- If network latency between server and client is relatively high. We can increase aggressiveness of the cache with
  proper HTTP headers.
- Lastly, we can introduce extra system to act as request cache. Most common one would be kv store like redis.
  From professional experience there's consideration to be made before introducing another layer of data storage.

  e.g: whether stale/ out-of-sync data is acceptable, since the extra system would not have knowledge of current db
  state, only on request frequency.