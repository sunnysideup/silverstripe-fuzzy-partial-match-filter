# Usage

Find records, even if the search term is misspelled.

```php

$obj = MyDataObject::create();
$obj->MyField = 'Test';
$obj->write();
$obj = MyDataObject::create();
$obj->MyField = 'Johnson Mike';
$obj->write();

echo PHP_EOL."Matches for Tezt:"
print_r(
    MyDataObject::get()
        ->filter(['MyField:FuzzyFilter' => 'Tezt'])
        ->count()
);

echo PHP_EOL."Matches for Mike Johnson:"
print_r(
    MyDataObject::get()
        ->filter(['MyField:FuzzyFilter' => 'Mike Johnson'])
        ->count()
);
```

If you are getting too many matches or too few, you can change the configuration:

Want less match? Do something like this:

```yml
Sunnysideup\FuzzyPartialMatchFilter\FuzzyPartialMatchFilter:
  min_chunk_length: 5
  min_chunk_match_percentage: 0.75
```

Want more match? Do something like this:

```yml
Sunnysideup\FuzzyPartialMatchFilter\FuzzyPartialMatchFilter:
  min_chunk_length: 2
  min_chunk_match_percentage: 0.3
```
