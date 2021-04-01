# Data Combinator for PHP 

When you want to list all possible combinations.

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

# Applications

* Generates all possible combinations for a command line tool
* Generates numerous variations for incoming data
* Produces structured data easily 

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

* [addConstant()](#addConstant) - adds a unique value
* [addSet()](#addSet) - adds a list of values
* [addLambda()](#addLambda) - calls an arbitrary function to generate a value
* [addPermute()](#addPermute) - creates all permutations from a list
* [addCombine()](#addCombine) - creates all combinations from a list 
* [addCopy()](#addCopy) - clones objects instead of copying them by value
* [addMatrix()](#addMatrix) - nests matrices within matrices
* [setClass()](#setClass) - switch from array representation to object

<a name="addConstant"></a>
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
See also [addCopy()]($addCopy) for cloning objects.

<a name="addSet"></a>
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

<a name="addLambda"></a>
### addLambda

This method adds a closure, a callback or an arrow function as a value. The closure will be called for each item to generate a new value. The closure will receive an (array) argument with all the previously created values (in the order of adding). That way, it may create a new value, based on previously generated values. 

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

<a name="addPermute"></a>
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

<a name="addCombine"></a>
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

<a name="addCopy"></a>
### addCopy

While [addConstant()]($addConstant) makes a value copy of the constant, addCopy clones the input object each time. They will look identical, at generation time, but are actually distinct objects.


```php

$m = new Datacombinator\Matrix();

// Create all combinaisons from the list : total 8 of them
$m->addClone('x', new \stdClass);
$m->addSet('i', [1, 2]);

$generated = $m->toArray();

```

<a name="addMatrix"></a>
### addMatrix

Adding a Matrix to another is the way to nest matrices. It allows the creation of objects through the combining process.

```php

$m = new Datacombinator\Matrix();
$m->addSet('i', [2, 3]);
$m->setClass(stdClass::class);

$n = new Datacombinator\Matrix();
$n->addSet('j', [5,7]);
$n->addMatrix('x', $m);
Array
(
    [j] => 5
    [x] => stdClass Object
        (
            [i] => 2
        )

)
Array
(
    [j] => 5
    [x] => stdClass Object
        (
            [i] => 3
        )

)
Array
(
    [j] => 7
    [x] => stdClass Object
        (
            [i] => 2
        )

)
Array
(
    [j] => 7
    [x] => stdClass Object
        (
            [i] => 3
        )

)
```

<a name="setClass"></a>
### setClass

By default, the matrix generates values of array types. This is the most versatile format. It is possible to turn those arrays into objects, by giving it a class. 

The object is created with an instanciation without arguments. Then, public properties are set by direct access. Private and protected properties are omitted; missing values are left untouched; extra values are omitted too.

```php

class Point {
	int $x, $y;
}

$m = new Datacombinator\Matrix();
$m->setClass(Point::class);
$m->addSet('x', [1,2]);
$m->addSet('y', [4,5]);

Point Object
(
    [x] => 1
    [y] => 4
)

```

You may also get a stdClass object by using its fully qualified name. Then, all passed values will be turned into a property. 

```php

class Point {
	int $x, $y;
}

$m = new Datacombinator\Matrix();
$m->setClass(\stdClass::class);
$m->addConstant('x', 1);
$m->addConstant('y', 2);

stdClass Object
(
    [x] => 1
    [y] => 2
)
```


## TODO

* add supports for setters and factories
* add support for references (currently, all by value)
* add supports for aliases (reusing a set of value that is already defined in another part of the generator)
* produces data list for documentation
* produce a JSON
* count() (when possible)
* Add coding convention
* FAQ/HOW-to