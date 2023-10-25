<?php

namespace SashaBo\NginxConfParser\Composers;

class HtmlComposer extends Composer
{
    protected const NEW_LINE = "<br>\n";
    protected const TAB = '&nbsp;&nbsp;&nbsp;&nbsp;';

    protected static function composeName(string $name): string
    {
        return '<span style="color: #009">'.parent::composeName($name).'</span>';
    }

    protected static function composeValue(string $value): string
    {
        $composed = parent::composeValue($value);
        return substr($composed, 0, 1) == '\''
            ? '<span style="color: #755; text-decoration: underline">'.$composed.'</span>'
            : '<span style="color: #900;">'.$composed.'</span>'
        ;
    }
}
