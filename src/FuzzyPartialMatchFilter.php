<?php

namespace Sunnysideup\FuzzyPartialMatchFilter;

use SilverStripe\ORM\Filters\SearchFilter;
use SilverStripe\ORM\DataQuery;
use SilverStripe\ORM\DB;
use InvalidArgumentException;

/**
 * Matches textual content with a LIKE '%keyword%' construct.
 */
class FuzzyPartialMatchFilter extends SearchFilter
{
    public function getSupportedModifiers()
    {
        return ['not'];
    }

    // /**
    //  * Apply the match filter to the given variable value
    //  *
    //  * @param string $value The raw value
    //  * @return string
    //  */
    // protected function getMatchPattern($value)
    // {
    //     return "%$value%";
    // }

    // public function matches(mixed $objectValue): bool
    // {
    //     $isCaseSensitive = $this->getCaseSensitive();
    //     if ($isCaseSensitive === null) {
    //         $isCaseSensitive = $this->getCaseSensitiveByCollation();
    //     }
    //     $caseSensitive = $isCaseSensitive ? '' : 'i';
    //     $negated = in_array('not', $this->getModifiers());
    //     $objectValueString = (string) $objectValue;

    //     // can't just cast to array, because that will convert null into an empty array
    //     $filterValues = $this->getValue();
    //     if (!is_array($filterValues)) {
    //         $filterValues = [$filterValues];
    //     }

    //     // This is essentially a in_array($objectValue, $filterValues) check, with some special handling.
    //     $hasMatch = false;
    //     foreach ($filterValues as $filterValue) {
    //         if (is_bool($objectValue)) {
    //             // A partial boolean match should match truthy and falsy values.
    //             $doesMatch = $objectValue == $filterValue;
    //         } else {
    //             $filterValue = (string) $filterValue;
    //             $regexSafeFilterValue = preg_quote($filterValue, '/');
    //             $doesMatch = preg_match('/' . $regexSafeFilterValue  . '/u' . $caseSensitive, $objectValueString);
    //         }
    //         // Any match is a match
    //         if ($doesMatch) {
    //             $hasMatch = true;
    //             break;
    //         }
    //     }

    //     // Respect "not" modifier.
    //     if ($negated) {
    //         $hasMatch = !$hasMatch;
    //     }

    //     return $hasMatch;
    // }

    protected function applyOne(DataQuery $query)
    {
        $this->model = $query->applyRelation($this->relation);
        $string = "(" . $this->getDbName() . ") LIKE '%" . $this->getValue() . "%' OR SOUNDEX('" . $this->getValue() . "') = SOUNDEX(" . $this->getDbName() . ")";
        return $query->where($string);
    }

    protected function excludeOne(DataQuery $query)
    {
        $this->model = $query->applyRelation($this->relation);
        $string = "(" . $this->getDbName() . ") NOT LIKE '%" . $this->getValue() . "%' AND SOUNDEX('" . $this->getValue() . "') != SOUNDEX(" . $this->getDbName() . ")";
        return $query->where($string);
    }



    public function isEmpty()
    {
        return $this->getValue() === [] || $this->getValue() === null || $this->getValue() === '';
    }
}
