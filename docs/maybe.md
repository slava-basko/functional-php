# Maybe

Usually we're thinking like that: "I have a $value, and I need to pass it to someFunction()".
This container force us to swap our thoughts to: "I have a $value, and I need apply someFunction() to it".
This allows us to encapsulate some logic inside container, like `is_null` checks.

```php
if(!is_null($value)) {
    // do something with $value
}
```

`Maybe` helps you avoid code duplication like above. `Maybe` is a container for a value that could present 
or not (it's like a Schrödinger's box with the cat inside).

Let's imagine that you need took `$article` from repository, take only 22 characters from title, and make it caps.
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

// use $articleTitle somewhere below
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

The point is that your methods and function return one type. Usually they return value or NULL, so it lead to 
additional IFs.

You may notice that Maybe can't tell what exactly happened. No entity in DB or it was connection issue?
User [Either](either.md) if error is matter for you.