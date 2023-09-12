# Maybe

`Maybe` is a container for a value that could present or not (it's like a Schrödinger's box with the cat inside). 
You don't know it until you look inside. You open it — you see a value.

You can consider this container as `is_null` check if you want
```php
if(!is_null($value)) {
    // do something with $value
}
```
`Maybe` helps you avoid code duplication like `if(!is_null($value))`.

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