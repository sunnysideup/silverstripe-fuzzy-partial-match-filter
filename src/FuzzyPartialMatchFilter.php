<?php

namespace Sunnysideup\FuzzyPartialMatchFilter;

use SilverStripe\ORM\DataQuery;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\SearchFilter;
use InvalidArgumentException;

/**
 * Matches textual content with a LIKE '%keyword%' construct.
 */
class FuzzyPartialMatchFilter extends PartialMatchFilter
{
    protected static $matchesStartsWith = false;
    protected static $matchesEndsWith = false;

    public function getSupportedModifiers()
    {
        return ['not', 'nocase', 'case'];
    }

    /**
     * Apply the match filter to the given variable value
     *
     * @param string $value The raw value
     * @return string
     */
    protected function getMatchPattern($value)
    {
        return "%$value% OR SOUNDEX($this->getDbName()) = SOUNDEX('$value')";
    }
}
