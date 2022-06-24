# libAsync

Stupid async implementation using await-generator

## Usage
```php
libAsync::doAsync(Closure $executor); // <-- Returns a promise
```
## Example
 - Fetch data from the internet
```php
use SOFe/AwaitGenerator/Await;
use Mcbeany/libAsync/libAsync;
use pocketmine/utils/Internet;

Await::f2c(function(){
    $response = yield from libAsync::doAsync(fn() => Internet::getUrl("example.com"));
    var_dump($response);
});
```
 - Save file asynchronously
```php
Await::g2c(
    libAsync::doAsync(fn() => file_put_contents('file.txt', 'Hello World!')),
    fn() => $this->getLogger()->info("Saved file successfully!")
);
```
## Note
> Do not pass any variables that cannot be serialized to AsyncTask
