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

    private static int $min_chunk_length = 3;
    private static float $min_chunk_match_percentage = 0.6;


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
        $searchForFragments = $this->searchForFragments($this->getDbName(), $this->getValue());
        $string = "(" . $searchForFragments . ") OR SOUNDEX('" . $this->getValue() . "') = SOUNDEX(" . $this->getDbName() . ")";
        return $query->where($string);
    }

    protected function excludeOne(DataQuery $query)
    {
        $this->model = $query->applyRelation($this->relation);
        $searchForFragments = $this->searchForFragments($this->getDbName(), $this->getValue(), true);
        $string = "(" . $searchForFragments . ") AND SOUNDEX('" . $this->getValue() . "') != SOUNDEX(" . $this->getDbName() . ")";
        return $query->where($string);
    }



    public function isEmpty()
    {
        return $this->getValue() === [] || $this->getValue() === null || $this->getValue() === '';
    }
    function searchForFragments(string $dbName, string $value, ?bool $negate = false): ?string
    {
        // Break the value into 3-character chunks
        $chunks = [];
        $chunkSize = $this->config()->get('min_chunk_length') ?: 3;
        $matchPercentage = $this->config()->get('min_chunk_match_percentage') ?: 0.75;
        if (strlen($value) < ($chunkSize + 1)) {
            return "($dbName) LIKE '%$value%'";
        }
        for ($i = 0; $i <= strlen($value) - $chunkSize; $i++) {
            $chunks[] = substr($value, $i, $chunkSize);
        }

        // Build the SQL condition to match chunks in the database field
        $chunkConditions = array_map(function ($chunk) use ($dbName) {
            return "($dbName) LIKE '%$chunk%'";
        }, $chunks);

        // Combine the conditions and calculate the percentage match
        $totalChunks = count($chunks);
        //Combines the conditions into a single expression, joined by +. In SQL, TRUE is treated as 1 and FALSE as 0. So, + adds up the matches.
        $sign = $negate ? '<' : '>';
        $query = "
            ((" . implode(') + (', $chunkConditions) . ") " . $sign . "= " . ceil($totalChunks * $matchPercentage) . ")
        ";

        return $query;
    }
}
