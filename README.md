# Declarative Schema

Uses doctrine/dbal to manage a declarative schema approach.

## Usage

This package has only been tested on MySQL but might work for other databases too.

Create a `schema.config.php` file in the directory where your `vendor` folder is. 
Go to [examples](examples/schema.config.php) to find an example of this file.

To create a migration use:

```PHP
php vendor/bin/schema make:schema table_name
```

The [DBAL docs](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/schema-representation.html#column) provide documentation on how to create new columns and indexes. Some Laravel like methods have also been added for ease of use. 

For an example schema file go to [examples](examples/example_schema.php).

Then you can call the following command to run the changes on the database.

```PHP
php vendor/bin/schema migrate:schema
```

