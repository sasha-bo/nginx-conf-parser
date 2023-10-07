# nginx-conf-parser

A PHP library for parsing nginx.conf files. No dependencies.

### Installing

`composer require sasha-bo/nginx-conf-parser`

### Usage

To parse nginx.conf from string use static method:

`SashaBo\NginxConfParser\Parser::parseString(string $source): array`

To parse from file:

`SashaBo\NginxConfParser\Parser::parseFile(string $path, bool $followIncludes = false): array`

Both return array<SashaBo\NginxConfParser\Row>

Row methods:

`getName(): string`

The name of nginx.conf parameter, for example 'server_name'.

`getValue(): array`

An array or words going after the name. For example, if the row is:

*server_name my-domain.com my-alias.com;*

getValue() returns `['my-domain.com', 'my-alias.com']`

`getRows(): array<Row>`

An array of rows in {...}. 
Each of them has getName(), getValue() and getRows() too.
So, a row with name 'http' contains rows with name 'server', and they -
rows with name 'location'.

### Behavior

The parser doesn't interpret nginx.conf data, just parses. 
It knows nothing about nginx parameters, just follows the syntax.
If your nginx.conf file contains wrong data, the parser won't throw 
exceptions while the structure of the file is correct.

The only command the parser understands is ***include*** if `$followIncludes`
is `true`. In this case, parser will parse also included file (or files if
there is a pattern like *sites-enabled/\*.conf*) and replace the 
***include*** row with rows from the included file.

### Composing nginx.conf

The package contains a tool for composing parsed (or generated) data back
to nginx.conf file. To do this, use static method
`SashaBo\NginxConfParser\Composer::compose(array $rows): string`



