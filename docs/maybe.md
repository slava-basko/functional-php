# Maybe

Normally, we think like this: "I have a \$value, so I pass it to someFunction()."
But `Maybe` flips it around: "I have a \$value, so I can apply someFunction() to it."

This shift lets you encapsulate logic inside the container — for example, common `is_null` checks:
```php
if(!is_null($value)) {
    // do something with $value
}
```

`Maybe` helps you avoid repetitive code like that. It acts as a container for a value that might be present — or not 
(like Schrödinger's cat in a box).

Let's say you need to fetch an `$article` from the repository, grab the first 22 characters of its title, and 
capitalize them:
```php
class ArticlesRepository
{
    public function get(int $id): array|null
    {
        return $this->entityManager->get($id);
    }
}

$article = $repository->get(120);

$articleTitle = null;
if (!is_null($article)) {
    $articleTitle = strtoupper(substr($article['title'], 0, 22));
}

// use $articleTitle later
```

Now let's modify repository, so it will return one type `Maybe`.
```php
class ArticlesRepository
{
    public function get(int $id): Maybe
    {
        return Maybe::of($this->entityManager->get($id));
    }
}

$articleTitle = $repository->get(120)->map(prop('title'))->map(take(22))->map('strtoupper');

// use $articleTitle somewhere below, you can call `$articleTitle->extract()` if you want to get value outside from container.
```

The point is that your methods and function return the same type. Without `Maybe`, you'd typically return either 
a value or null, forcing you to scatter `if` checks everywhere.

One thing to note. The `Maybe` doesn't tell you why there's no value. Was it a missing entity in the DB? A connection 
issue? If you care about why, use [Either](either.md) instead.
