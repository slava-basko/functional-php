# Functional PHP
Collection of PHP functions that allows you to write code in a declarative way.

The purpose of this library is not to replace imperative and OOP. They can be combined, and I believe
they should be combined because any of these approaches is not a silver bullet.

## Examples

I will omit the theory about functional programming because you can find a lot of information about it yourself.
But I want to show you examples.

### Collection example
Let's imagine that you are using collection lib, and you want to upper all elements.
You need to write things like this:
```php
$collection = collect(['one']);
$collection->map(function ($value) {
    return strtoupper($value);
});
```
Because you can get an error like `ArgumentCountError : strtoupper() expects exactly 1 argument, X given` 
when you will write `$collection->map('strtoupper');`.
Only user defined functions does not throw an exception when called with more arguments. But you can do this:
```php
$collection = collect(['one']);
$collection->map(unary('strtoupper'));
```
Bam! You get less bloated code without `function`, `{`, `return`, `}`, `;`. `unary` is a higher-order function, it takes
function with any arity and return new function that accept only one argument.

That's what I mean when I talk about combining imperative/OOP and functional code.

One more example with the collection. We need to filter users by `isActive` method for example.
```php
$collection = collect([$user1, $user2, $user3]);
$collection->filter(function ($user) {
    return $user->isActive();
});

// VS

$collection = collect([$user1, $user2, $user3]);
$collection->filter(invoker('isActive'));
```

### Point-free example
Now let's consider the second example when we need to calculate qty of items in order.
```php
$products = [
    [
        'description' => 't-shirt',
        'qty' => 2,
        'value' => 20
    ],
    [
        'description' => 'jeans',
        'qty' => 1,
        'value' => 30
    ],
    [
        'description' => 'boots',
        'qty' => 1,
        'value' => 40
    ],
];

$imperativeTotalQty = 0;
foreach ($products as $product) {
    $imperativeTotalQty += $product['qty'];
}

// OR
$totalQty = compose(sum, pluck('qty'))($products);
```

You can read code `compose(sum, pluck('qty'))` like `sum of 'quantity' properties`.
Ok, I understand that this could be a bit odd for you. You get used to writing code differently.

What we also did, is that we created a "point free" function (tacit programming).
```php
function getTotalQty($products) {
    $totalQty = 0;
    foreach ($products as $product) {
        $totalQty += $product['qty'];
    }
    return $totalQty;
}

// VS

$getTotalQty = compose(sum, pluck('qty'));
```

In the first version we created regular function. We need to operate with the `$products`, `$product` 
and `$totalQty`. We tell the machine "how to" calculate qty.
> Hey, computer. Create $totalQty with the initial 0 value. 
> Now iterate through $products.
> Add the value of $product 'qty' property to the value of $totalQty variable.
> Return the value of $totalQty variable.

But the second version is point free. We tell "what we want", without details.
> Hey, computer. Give me a sum of 'qty' properties.

### Pipe and partial application
We have a $product[] and we need to create a common description from the 'description' property of each one.
So, here are the basic steps:
1. Fetch property 'description' from products.
2. Strip whitespace from the beginning and end of each value.
3. Remove empty elements.
4. Join elements with commas.
5. Cut generated descriptions up to 34 characters.
6. Trim the comma at the end if present.

The imperative way could be:
```php
$commonDescription = trim(substr(implode(', ', array_filter(array_map('trim', $descriptions), 'strlen')), 0, 34), ', ');
// OR
$commonDescription = trim(
    substr(
        implode(
            ', ', 
            array_filter(
                array_map('trim', $descriptions), 
                'strlen'
            )
        )
        , 0, 34
    ),
    ', '
);
```
Quite a big cognitive load. Let's try to reorder it and make it more readable.
```php
$descriptions = array_map('trim', $descriptions);
$descriptions = array_filter($descriptions, 'strlen');
$description = implode(', ', $descriptions);
$description = substr($description, 0, 34);
$commonDescription = trim($description, ', ');
```
Now it's more readable, but we need to mess with states.

The functional code could be like this:
```php
$commonDescription = pipe(
    pluck('description'),
    map(unary('trim')),
    select(unary('strlen')),
    join(', '),
    take(34),
    partial_r('trim', ', ')
)($products);
```
This is precisely what we need. It's in a natural order. No intermediate states.

### What about some real-life example?
No problem, this project has a doc auto-generation script.
Written in an entirely point-free manner.

[Show me your doc_generator.php](internal/doc_generator.php)

No variable were harmed in making this script (c).

## General

### Import functions
Add `use Functional as f;` on top of your PHP file or use `use function Functional\function_name`.
The last option is used in the docs, and it's the preferred way starting with PHP 5.6.

### Name convention
The `snake_case` is used to be closer to a PHP native functions.

### "Data last" principe
The data to be operated on is generally supplied last (last functions argument).
Functions is more convenient for currying in this way.

### Functions are curried by default
This allows us to be more efficient in building new functions from old ones simply by not supplying the final parameters.

---
The last two points together make it easy to build functions as sequences of simpler functions, 
each of which transforms the data and passes it along to the next.

## Docs
Examples is cool, but now it's time for "boring" part.

[Read the docs](docs/functions.md)

## Optional
Almost the same as `Maybe`. But `Maybe` is more about technical layer, and `Optional` is about business cases.
Let's take CRUD operation as an example. Does a `null` `$description` mean "remove the description", 
or "skip setting the description"?
```php
class EditArticle {
    private function __construct(
        public readonly int $id,
        public readonly string|null $title,
        public readonly string|null $description,
    ) {}

    public static function fromPost(array $post): self
    {
        Assert::keyExists($post, 'id');
        Assert::positiveInt(prop('id', $post));
                
        return new self(
            prop('id', $post), 
            prop('title', $post), 
            prop('description', $post)
        );
    }
}
```
The usage side.
```php
class HandleEditArticle
{
    public function __construct(private readonly Articles $articles) {}
    
    public function __invoke(EditArticle $command): void {
        $article = $this->articles->get($command->id);
        
        if ($command->title !== null) {
            // update title only when provided
            $article->setTitle($command->title);
        }
        
        // Description always updating. Maybe we just forgot extract it from the payload?
        // Who is responsible for deciding "optional field" vs "remove description when not provided": the command,
        // or the command handler?
        // Is this a bug, or correct behavior?
        $article->setDescription($command->description);
        
        $this->articles->save($article);
    }
}
```

Now let's use `Optional`.
```php
class EditArticle {
    private function __construct(
        public readonly int $id,
        public readonly Optional $title,
        public readonly Optional $description,
    ) {}

    public static function fromPost(array $post): self
    {
        Assert::keyExists($post, 'id');
        Assert::positiveInt(prop('id', $post));
                
        return new self(
            prop('id', $post), 
            Optional::fromArrayKey('title', $post), 
            Optional::fromArrayKey('description', $post)
        );
    }
}
```

The handler.
```php
class HandleEditArticle
{
    public function __construct(private readonly Articles $articles) {}
    
    public function __invoke(EditArticle $command): void {
        $article = $this->articles->get($command->id);
        
        // Only called if a fields has a provided value.
        $command->title->match([$article, 'setTitle'], N);
        $command->description->match([$article, 'setDescription'], N);
        
        $this->articles->save($article);
    }
}
```
What we have here:
* Better clarity about the optional nature of specific fields
* No conditional logic (less static analysis and testing efforts)
* Strict behavior: field provided — will be updated, not provided — nothing happen

## Influenced by

https://ramdajs.com \
https://github.com/lstrojny/functional-php \
https://github.com/ace411/bingo-functional \
https://github.com/ircmaxell/monad-php \
https://github.com/yuhanz/ramda-php \
And many other libs/articles/etc.

Why just not use one from the list? 


Well, RamdaJS obviously not our choice because it is great JS lib but we are using PHP. 
lstrojny/functional-php good choice but functions are not curried by default and there is no "data last principle". 
ircmaxell/monad-php wonderful monad implementation in my opinion, but it's only monad with no additional functions. 
yuhanz/ramda-php abandoned RamdaJS port plus I don't like all these public static properties.



## How to run tests

PHP 5
```shell
docker run -v `pwd`:/var/www --rm feitosa/php55-with-composer composer install
docker run -v `pwd`:/var/www --rm feitosa/php55-with-composer vendor/bin/phpunit
```

PHP 8
```shell
docker run -v `pwd`:/var/www --rm composer:2.5.8 composer install -d /var/www/
docker run -v `pwd`:/var/www --rm php:8.2-cli var/www/vendor/bin/phpunit /var/www/ -c /var/www/phpunit.xml.dist
```