# rowCloner: Relational database row cloning tool #
This tool began life as a simple proof of concept, based on a [question posed in the phpfreaks.com](https://forums.phpfreaks.com/topic/315930-writing-to-m) forums.

- The tool provides a web interface to select a row in a table by id.
- It selects that row, and empties the id value
- Other columns to null can be set optionally
- It then inserts that row back into the table

This project uses the Doctrine DBAL layer.  It was meant to be a quick and dirty proof of concept, created in minimal time.  There is limited error checking and no real security.  If you want to actually use this tool, you will need to secure it from the world using some other tool.  You can run it locally under Docker or as a development project, or on an intranet if you find it useful. The original user who posted the question was probably creating a development tool to manipulate test data.

As databases typically have relationships, unique indexes and the like, this tool would only be useful in simple cases.

## Configuration ##
Create a .env.local file copied/based on the .env.local.template file provided.  Change the relevant database connection variables as needed.  Examine the comments and variables for help.

## Environment Setup ##
Run this in the project directory:
```
composer install
```
This should work with the php local server, and was designed and tested using the symfony cli.

Also Run from the project directory:

```
symfony server:start
```

With some other web server, set the webroot to /path/to/rowCloner/public

## UI ##
I wanted a css framework that was fast and easy to use, and could be included from a CDN.  I used [Bulma](https://bulma.io/)

## Components ##
This project relies primarily on Doctrine DBA, but also uses monolog and symfony dotenv

## How it was created ##
This project used a number of techniques that PHP developers will typically use
 - started with a composer.json generated via composer --init 
 - public script/files in a /public directory
 - Use of components added via composer require
 - PSR-4 autoloading
 - project files mapped to namespace App/
 - configuration taking advantage of .env.* files
 - Uses a trait for generic shared debug routines
 - html5 markup with a css framework

## Best Trick ##
The nicest trick, is facilitated by Doctrine.  The cloned/duplicate row query is very easy thanks to the [DBAL connection insert method](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/data-retrieval-and-manipulation.html#insert).

## Design issues ##
There are numerous design issues and ways this could be improved and made more usable.  If you would like to improve it, I'll consider pull requests. You can file issues as well.  I made this on a whim and as a challenge and for practice.  Please keep this in mind.    

## Logging ##
As a quick and dirty proof of concept, this app logs a lot of information both to firephp (if you have that setup in your browser) and to a log file in the log directory.  For example, all db credentials are dumped on every request.  Change RC_DEBUG to false if you want to turn that off.

## Where are the tests? ##
Yes, this should have Unit tests.

This project was done as a proof of concept, in a day, start to finish. I did not use a framework, as I wanted this to be minimal and self contained, but a lot of time was wasted on things in the RequestHandler class, that would have been built into a framework like symfony.

If I have some spare time, I might add unit tests, but I have no current plan to do so. With that said, I have written many unit tests and have found them very useful, as they often force re-evaluation of design decisions you made.

For ongoing development, unit (and other types of) tests are an indispensible part of the enhancement process, and allow you to maintain quality and find bugs before they go into production.  You also can't take advantage of CI/CD without them.

## Looking to practice unit testing? ##
If you are looking for a way to practice and improve, try forking this repo, making a branch and adding tests.

You need to [require phpunit](https://phpunit.de/getting-started/phpunit-9.html). As of the time this was written in feb of 2023, you would use composer to do that like this.

```
composer require --dev phpunit/phpunit ^9
```
At minimum you want to write tests for these classes
- RequestHandler
- RowCloner





