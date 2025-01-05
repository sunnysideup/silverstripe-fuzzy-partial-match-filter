# Usage

Find records, even if the search term is misspelled. 

```php

$obj = MyDataObject::create();
$obj->Title = 'Test';
$obj->write();

print_r(
    MyDataObject::get()
        ->filter('MyField:Fuzzy' => 'Tessst'])
        ->count()
);
