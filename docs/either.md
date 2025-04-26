# Either

Almost that same as [Maybe](maybe.md) but it holds two values, the `right` one (aka success), and the `left` (aka error).

Let's take the same example from `Maybe`, but tweak it a bit:
```php
class ArticlesRepository
{
    public function get(int $id): Either
    {
        try {
            $possibleArticle = $this->entityManager->get($id);
            
            return is_null($possibleArticle) ? Either::left('Not found') : Either::right($possibleArticle);
        } catch (DatabaseException $exception) {
            return Either::left($exception);
        }
    }
}

$articleTitle = $repository->get(120)->map(prop('title'))->map(take(22))->map('strtoupper');
```
We can safely apply the same functions (like `take` and `strtoupper`) to the value.

Later, somewhere in your code:
```php
$articleTitle->match(
    partial($view->addVar(...), 'articleTitle'),
    $errorHandler->handle(...)
);

// Equals to

$possibleArticleTitle = $articleTitle->extract();
if ($possibleArticleTitle) {
    $view->addVar('articleTitle', $possibleArticleTitle);
} else {
    $errorHandler->handle($possibleArticleTitle);
}
```
