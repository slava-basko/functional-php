# Optional

Almost the same as [Maybe](maybe.md). But `Maybe` is more about technical layer, and `Optional` is about business cases.
The `NULL` in the context of `Maybe` is just `nothing`, no value. But in the context of `Optional` the `NULL` is a 
valid value, operation will be performed.

Let's take CRUD operation as an example. Does a `null` `$description` mean "remove the description",
or "skip updating the description"?
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

The usage side:
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

Now, let's use `Optional`:
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
            Optional::fromProp('title', $post), 
            Optional::fromProp('description', $post)
        );
    }
}
```

The handler:
```php
class HandleEditArticle
{
    public function __construct(private readonly Articles $articles) {}
    
    public function __invoke(EditArticle $command): void {
        $article = $this->articles->get($command->id);
        
        // Only called if a fields has a provided value.
        $command->title->match($article->setTitle(...), noop);
        $command->description->match($article->setDescription(...), noop);
        
        $this->articles->save($article);
    }
}
```

What we have here:
* Better clarity about the optional nature of specific fields
* No conditional logic (less static analysis and testing efforts)
* Strict behavior: field provided -> update it, field missing -> do nothing
