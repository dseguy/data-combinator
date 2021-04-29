# Data Combinator for PHP 

When you want to list all possible combinations of various lists of data.

I.e., from ```[[1, 2], [3, 4]] ``` to 

```
[
	[1, 3], 
	[1, 4],
	[2, 3], 
	[2, 4],
] 
```

This component allows you to create large dataset by combining each possible values with the others. 

# Applications

* Generates all possible combinations for a command line tool
* Generates all variations for incoming data
* Produces structured data 

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

* [Common behaviors](#Common-behaviors) - common behaviors to the public methods
* [generate()](#generate) - yields one value
* [addConstant()](#addConstant) - adds a unique value
* [addSet()](#addSet) - adds a list of values
* [addLambda()](#addLambda) - calls an arbitrary function to generate a value
* [addPermute()](#addPermute) - creates all permutations from a list
* [addCombine()](#addCombine) - creates all combinations from a list 
* [addCopy()](#addCopy) - clones objects instead of copying them by value
* [addMatrix()](#addMatrix) - nests matrices within matrices
* [addAlias()](#addAlias) - reuses a previously generateur value
* [addSequence()](#addSequence) - creates values that differ by their identifier
* [setClass()](#setClass) - selects the resulting object type : array, list or object
* [count()](#count) - estimates the number of elements that will be produced
* [toArray()](#toArray) - returns all the possible combinaisons as an array

<a name="Common-behaviors"></a>
### Common-behaviors

Public methods are called with at least two arguments ; the name of the value, and the actual value or a generator for that value. 

Each value name is a string, or null. When the name is null, automatic id is generated, starting from 0 (a la PHP).

Index for arrays may be provided with a string, which will be turned into an array index by PHP. Using null and Matrix::TYPE_ARRAY has the same effect as using Matrix::TYPE_LIST. Using Matrix::TYPE_LIST with named values is legit : the names will be dropped at generation time, yet, they provide some readability at configuration time. 

It is possible to overwrite a previously set property by adding it again. 

It is recommended to avoid using any other format beside string, integers in strings and null. 

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

<a name="generate"></a>
### generate

Returns a generator for usage with foreach() structures. 

The generator will return the same values as the `toArray()` method, but yielded, one by one. 
After a full generation, the generate() method will yield again the same values, as per cache. 

```php

$m = new Datacombinator\Matrix();
$m->addSet('x', [1, [2], 3]);
$m->addConstant('y', 4);

foreach($m->generate() as $array) {
    print_r($array);
}
```

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

This method adds a closure, a callback or an arrow function as a value. The closure will be called for each item to generate a new value. 

The closure receives an (array) argument with all the previously created values. That way, it may create a new value, based on previously generated values. That array is filled in the order of addition to the Matrix : in particular, this means that all values are not always available, since some of them may still be pending. Also, values added as 'alias', are processed last, and are not available. 

The provided argument is an array. The type of its values are the type of values added to the Matrix. For sub-matrices, it may be another array or an object, depending on configuration.

When using a closure or an arrow function, it is possible to access a unique Identifier with the `$this->uniqueId` property. The uniqueId is an int, starting at 1, and incremented each usage. 

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

$m = new Matrix();

// No argument for this closure, as we don't need it
$m->addLambda('x', function () { return $this->uniqueId;});
$m->addSet('y', [5,6]);

(
    [x] => 1
    [y] => 5
)
(
    [x] => 2
    [y] => 6
)

```

```php

$m = new Datacombinator\Matrix();

// No argument for this closure, as we don't need it
$a = $m->addConstant('a', 'A');
$m->addAlias('b', $a);
$m->addLambda('x', function ($r) { return $r['a'].($r['b'] ?? 'No B').($r['c'] ?? 'No C')});
$m->addConstant('c', 'C');

print_r($m->toArray());

Array
(
    [0] => Array
        (
            [a] => A
            // No B, because it is an alias
            // No C, because it is defined later. It may be moved before 'x' to get access to it
            [x] => ANo BNo C
            [c] => C
            [b] => A
        )

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

Adding a Matrix to another is the way to nest matrices. It also allows the creation of objects through the combining process.

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

Matrices have 2 options : 
* Cache, which caches the values once they are generated. 
  * Matrix::WITHOUT_CACHE re-generate the Matrix each time, 
  * Matrix::WITH_CACHE generates the cache once, and keep reusing it later.
* Write mode, which configures the behavior in case of multiple definitions 
  * Matrix::OVERWRITE is the default. The value is replaced with the new definition.
  * Matrix::SKIP skips any previously defined value
  * Matrix::WARN throws an exception when a previously defined name is reused.

<a name="addAlias"></a>
### addAlias

Reuse a previously generated value in another slot of the generated data. This is useful when the same value has to be set at two (or more) slots.

```php

$m = new Matrix();
$i = $m->addSet('i', [2, 3]);
$m->addAlias('j', $i);

print_r($m->toArray());

Array
(
    [0] => Array
        (
            [i] => 2
            [j] => 2
        )

    [1] => Array
        (
            [i] => 3
            [j] => 3
        )

)
```

Aliases may be added in any order : it is possible to call an alias created in a sub-matrix from the top-matrix, or vice-versa. 

Aliases are processed as the last elements in a Matrix. They might not be available in a Lambda call, which will always happen before.

<a name="addSequence"></a>
### addSequence

Generates sequential data, from min to max, optionally updated by a closure. 

```php

$m = new Matrix();
$i = $m->addSequence('i', 0, 10);

print_r($m->toArray());

Array
(
    [0] => Array
        (
            [i] => 0
        )
    [1] => Array
        (
            [i] => 1
        )
    [2] => Array
        (
            [i] => 2
        )
// ....
    [9] => Array
        (
            [i] => 9
        )
)
```

<a name="setClass"></a>
### setClass

By default, the matrix generates values of type array. This is the most versatile format. 

It is possible to turn those arrays into objects, by giving it a class, or into a list (automatically indexed array) by using the Matrix::TYPE_LIST constant. It is possible to force the default type with the Matrix::TYPE_ARRAY constant.

The object is created with an instanciation without arguments. Then, public properties are set by public access. Private and protected properties are omitted; missing values are left untouched; extra values are omitted too.

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

<a name="count"></a>
### count

Counts the number of elements to be produced. 

```php

$m = new Datacombinator\Matrix();
$m->addSet('x', [1,2]);
$m->addSet('y', [4,5]);
print $m->count()." elements";


// 4 elements
```

<a name="toArray"></a>
### toArray

Returns all the possible combinaisons as an array

```php

$m = new Datacombinator\Matrix();
$m->addSet('x', [1,2]);
$m->addSet('y', [4,5]);
print_r($m->toArray());

Array
(
    [0] => Array
        (
            [x] => 1
            [y] => 4
        )

    [1] => Array
        (
            [x] => 1
            [y] => 5
        )

    [2] => Array
        (
            [x] => 2
            [y] => 4
        )

    [3] => Array
        (
            [x] => 2
            [y] => 5
        )

)
```

## FAQ

### How to shuffle the results? 

Use the toArray() method, and apply the PHP native shuffle() function on it.

### How to limit the results? 

Use foreach() with the generate() method, and count the number of element needed. Then, break when all the needed results were yielded.
