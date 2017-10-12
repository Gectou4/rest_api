# G4Api a sample api

It's a simple sample of what can be a little REST API.

How to Configure
------------------

Database
------------------

You have to create a database. A schema and the data are available in `share/sql/rest_api.sql`

Then Open `src/Config/DB.php` Edit getConfig function for user, pwd and dsn with an user granted for the created database.

Sample: 
```
$default = [
            'user'      => 'root',
            'pwd'       => '',
            'dsn'       => 'mysql:host=localhost;dbname=rest_api;charset=utf8'
];
```

Route
------------------
 
Open src/Config/Route.php You can edit or add a route.
To add a route simply add a new match in the list in the order to proceed

Sample: 
```
->match(
    'POST|PUT',
    '/task/(\d+)',
    function ($id) { 
        return [
            'controller' => 'Task',
            'action' => 'editTask',
            'params' => ['id' => $id], 
        ]; 
    }
)

```

First arg is Http Method (PUT, HEAD, GET, POST, DELETE), you can set on or more methods for a route by using "|" Second arg is the route Pattern for a Regex Last arg is the return function, each arg must match the pattern parenthesis, the function must return an array with controller to use, action to call and an optional params list

Add a controller
------------------

Add a class inside scr/Controller like this :
```
namespace G4\Api\Controller;

class MyClass extends ControllerAbstract
{
    public function getIndexAction()
    {
        return "Hello World !";
    }
}
```

PHPUnit
------------------
Some simple test are written. They require a valid database connection.

Composer
------------------
Composer is used for PHPUnit and PSR-4 autoload. Without it the Api will use App/PSR-4 autoload
