# Data Combinator for PHP 

When you want to list all possible combinaisons.

From ```[[1, 2], [3, 4]] ``` to 

```
[
	[1, 3], 
	[1, 4],
	[2, 3], 
	[2, 4],
] 
```

Allows you to create lots of data by combining them together! 

### Installation

With [Composer](https://getcomposer.org/), do the timeless

```sh
$ composer require dseguy/data-combinator
```

### Simple example


```php
require 'vendor/autoload.php';

use Datacombinator\Matrix;

$m = new Datacombinator\Matrix();
$m->addSet('x', [1,2,3]);
$m->addSet('y', [4,5,6]);

foreach($m as $value) {
    print_r($value);
}

```

This generates all combinaisons for 1,2,3 with 4,5,6. A total of 9 array.

```
(
    [x] => 1
    [y] => 4
)
Array
(
    [x] => 1
    [y] => 5
)
Array
(
    [x] => 1
    [y] => 6
)
... 
Array
(
    [x] => 3
    [y] => 6
)
```

## APIs

### setClass

By default, the matrix generates values of array types. This is the most versatile format. It is possible to turn those arrays into objects, by giving it a class. 

The object is created by a call to new without arguments. Then, public properties are set by direct access. Private and protected properties are omitted; missing values are left untouched; extra values are omitted too.

```php

class Point {
	int $x, $y;
}

$m = new Datacombinator\Matrix();
$m->setClass(Point::class);
$m->addSet('x', [1,2]);
$m->addSet('y', [4,5]);

foreach($m->generate() as $value) {
    print_r($value);
}

```

### addConstant

This adds a unique value to the Matrix. No repetition with this one. Note that the value might be an array.

```php

$m = new Datacombinator\Matrix();
$m->addConstant('x', 2);
$m->addConstant('y', 3);

Array
(
    [x] => 2
    [y] => 3
)
```

### AddSet

This adds a list of values to the Matrix. Each value will be repeated once. Provide an array of arrays, to combine those arrays.


```php

$m = new Datacombinator\Matrix();
$m->addSet('x', [1, [2], 3]);
$m->addConstant('y', 4);

(
    [x] => 1
    [y] => 4
)
Array
(
    [x] => Array (
            2
            )
    [y] => 4
)
Array
(
    [x] => 3
    [y] => 4
)

```

### addLambda

This adds a closure as a value. The closure will be called for each item, with all the previously created values as argument (array). The returned value is used as the generated value. 

```php

$m = new Datacombinator\Matrix();

// No argument for this closure, as we don't need it
$m->addLambda('x', function () { return rand(0, 10);});

// This closure takes the previously created values as input
// the range of random values is now twice as large
$m->addLambda('y', function ($value) { return rand(0, 2 * $value['x']);});

// the closure are called each time once, so this matrix as one element, with 2 closure calls
(
    [x] => 4
    [y] => 7
)
```

### addPermute

Uses the list as one argument, and generates all possible permutations of the values in it. 

```php

$m = new Datacombinator\Matrix();

// Create all permutation in the list : total 6 of them
$m->addPermute('x', [1, 2, 3]);


(
    [x] => Array
        (
            [0] => 1
            [1] => 2
            [2] => 3
        )

)
Array
(
    [x] => Array
        (
            [0] => 1
            [1] => 3
            [2] => 2
        )

)
Array
(
    [x] => Array
        (
            [0] => 2
            [1] => 1
            [2] => 3
        )

)
Array
(
    [x] => Array
        (
            [0] => 2
            [1] => 3
            [2] => 1
        )

)
Array
(
    [x] => Array
        (
            [0] => 3
            [1] => 1
            [2] => 2
        )

)
Array
(
    [x] => Array
        (
            [0] => 3
            [1] => 2
            [2] => 1
        )

)
```


### addCombine

Uses the list as one set, and generates all possible combinaisons of them, from none (empty array) to all of them.

```php

$m = new Datacombinator\Matrix();

// Create all combinaisons from the list : total 8 of them
$m->addCombine('x', [1, 2, 3]);


(
    [x] => Array
        (
        )

)
Array
(
    [x] => Array
        (
            [0] => 1
        )

)
Array
(
    [x] => Array
        (
            [0] => 2
        )

)
Array
(
    [x] => Array
        (
            [0] => 1
            [1] => 2
        )

)
Array
(
    [x] => Array
        (
            [0] => 3
        )

)
Array
(
    [x] => Array
        (
            [0] => 1
            [1] => 3
        )

)
Array
(
    [x] => Array
        (
            [0] => 2
            [1] => 3
        )

)
Array
(
    [x] => Array
        (
            [0] => 1
            [1] => 2
            [2] => 3
        )

)
```

### addCopy

While addConstant makes a value copy of the constant, addCopy clones the input object each time. They will look identical, at generation time, but are actually distinct objects.


```php

$m = new Datacombinator\Matrix();

// Create all combinaisons from the list : total 8 of them
$m->addClone('x', new \stdClass);
$m->addSet('i', [1, 2]);

$generated = $m->toArray();

$m[0]->y = 1;
print_r($m);

```
### addMatrix

Adding a Matrix to another is the way to nest matrices. 

```php

$m = new Datacombinator\Matrix();
$m->addSet('i', [2, 3]);

$n = new Datacombinator\Matrix();
$n->addSet('j', [5,7]);
$n->addMatrix('x', $m);

(
    [j] => 5
    [x] => Array
        (
            [i] => 2
        )

)
Array
(
    [j] => 5
    [x] => Array
        (
            [i] => 3
        )

)
Array
(
    [j] => 7
    [x] => Array
        (
            [i] => 2
        )

)
Array
(
    [j] => 7
    [x] => Array
        (
            [i] => 3
        )

)
```

## How-to



## TODO

* add support for other closure type (arrow function, callbacks...)
* add supports for iterators and generators
* add supports for setters and factories
* add supports for aliases (reusing a set of value that is already valid)
* produces data list for documentation
* produce a JSON
* count() (when possible)
* Add tests
* Add coding convention
