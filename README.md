# rowCloner: Relational database row cloning tool #
This tool began life as a simple proof of concept, based on a [question posed in the phpfreaks.com](https://forums.phpfreaks.com/topic/315930-writing-to-m) forums].

- The tool provides a web interface to select a row in a table by id.
- It selects that row, and empties the id value
- Other columns to null can be set optionally
- It then inserts that row back into the table

This project uses the Doctrine DBAL layer.  It was meant to be a quick and dirty proof of concept, created in minimal time.  There is limited error checking and no security

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

With some other web server, set the webroot to /path/to/rowCloner/public

## UI ##
I wanted a css framework that was fast and easy to use, and could be included from a CDN.  I used [Bulma](https://bulma.io/)

## Components ##
This project relies primarily on Doctrine DBA, but also uses monolog and symfony dotenv

## Best Trick ##
The nicest trick, is facilitated by Doctrine.  The cloned/duplicate row query is very easy thanks to the [DBAL connection insert method](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/data-retrieval-and-manipulation.html#insert).

## Design issues ##
There are numerous design issues and ways this could be improved and made more usable.  If you would like to improve it, I'll consider pull requests. File issues as well.

## Logging ##
As a proof of concept, this logs a lot of information both to firephp (if you have that setup in your browser) and to a log file in the log directory.  Change RC_DEBUG to false if you want to turn that off.

## Where are the tests? ##
Yes, this should have Unit tests. For a project designed, coded, tested and debugged in less than one day.

This project was done as a proof of concept, in a day, start to finish.  I did not use a framework, as I wanted this to be minimal and self contained, but a lot of time was wasted on things in the RequestHandler class, that would have been built into a framework like symfony.
