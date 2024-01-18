# nginx-conf-parser

A PHP library for parsing nginx.conf files. No dependencies.

## Installation

`composer require sashabo/nginx-conf-parser`

## Usage

To parse nginx.conf from string use static method:

`SashaBo\NginxConfParser\Parser::parseString(string $source): array`

To parse from file:

`SashaBo\NginxConfParser\Parser::parseFile(string $path, bool $followIncludes = false): array`

Both return ```array<SashaBo\NginxConfParser\Row>```:

```php
class Row
{
    public readonly string $name;
    /** @var array<string> */
    public readonly array $values;
    /** @var array<self> */
    public readonly array $rows;
    public readonly ?string $file;
    public readonly int $position;
    public readonly int $line;
    public readonly int $linePosition;
    public readonly int $length;
}
```

`name` - the name of nginx.conf parameter.

`values` - an array or words going after the name. For example:

```
name: 'server_name'
values: ['my-domain.com', 'my-alias.com']
```

`rows` - an array of rows in {...}. 

So, a row with name *'http'* contains rows with name *'server'*, and they -
rows with name *'location'*.

`position`, `line` and `linePosition` start from 0. For the *'server_name'* 
nginx parameter these three values describe the position of 's' symbol.

`length` is counted from the first symbol of the name ('s' for 
*'server_name'*) to the ';' or '}' symbol including them. So you can use 
`position` and `length` to remove or replace the whole nginx parameter or 
the block of parameters with substr_replace, and your nginx.conf will be 
still valid.

## Behavior

The parser doesn't interpret nginx.conf data, just parses. 
It knows nothing about nginx parameters, just follows the syntax.
If your nginx.conf file contains wrong data, the parser won't throw 
exceptions while the structure of the file is correct.

The only command the parser understands is *include* if `$followIncludes`
is `true`. In this case, parser will parse also included file (or few files 
if there is a pattern like *sites-enabled/\*.conf*) and replace the 
*include* row with rows from the included file.

## Composing nginx.conf

The package contains a tool for composing parsed (or generated) data back
to nginx.conf file. To do this, use static method
`SashaBo\NginxConfParser\Composer::compose(array $rows): string`



