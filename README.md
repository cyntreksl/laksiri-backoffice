## API Documentation

### API documentation is available at the following URL:
```bash
http://localhost:8000/docs
```

We're using [Scribe](https://scribe.knuckles.wtf/laravel) to generate API documentation. To generate the documentation, run the following command:

```bash
php artisan scribe:generate
```

### Usage

Add `docblocks` to your routes or controllers:

```php
/**
 * @group Authentication
 * 
 * Login
 * 
 * @bodyParam username string required The username. Example: John
 * @bodyParam password string required The password. Example: secret

    * @response 200 {
    *  "message": "Login successful"
    * }
    * @response 422 {
    *  "message": "The given data was invalid.",
    * }
    */
    public function Login(LoginRequest $request)
    {
        // Your code here
    }
```


### Tags
- `@group`: Use this tag to group routes together in the documentation, providing a clear organization of related routes.
- `@bodyParam`: This tag describes the request body parameters. You can specify the parameter type, indicate if it's required, and provide an example. It's automatically detected from the request validation rules.
- `@response`: This tag describes the response. You can specify the response status code and provide an example response. It's automatically detected from the controller method's return type. You can also add custom response examples using this tag.
- After adding or editing the docblocks in your routes or controllers, remember to run `php artisan scribe:generate` to update the documentation.


## Code Linting and Formatting

We're using [Laravel Pint](https://laravel.com/docs/11.x/pint) to lint and format our code. To lint and format your code, run the following command before committing your changes:

```bash
./vendor/bin/pint
```

