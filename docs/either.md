# Either

Almost that same as [Maybe](maybe.md) but it holds two values, the right one aka success, and the left aka error.

Lets took the same example from `Maybe` but change it a bit.
```php
class ArticlesRepository
{
    public function get(int $id): Either
    {
        try {
            return Either::right($this->entityManager->get($id));
        } catch (DatabaseException $exception) {
            return Either::left($exception);
        }
    }
}

$articleTitle = $repository->get(120)->map(prop('title'))->map(take(22))->map('strtoupper');
```
We can safely apply the same functions (like `take` and `strtoupper`) to our value.

Then somewhere in your code:
```php
$articleTitle->match(
    partial([$view, 'addVar'], 'articleTitle'),
    [$errorHandler, 'handle']
);

// Equals to

$possibleArticleTitle = $articleTitle->extract();
if ($possibleArticleTitle) {
    $view->addVar('articleTitle', $possibleArticleTitle);
} else {
    $errorHandler->handle($possibleArticleTitle);
}
```