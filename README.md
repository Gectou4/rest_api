# G4Api a sample api

Configure
------------------

**Database**

You have to create a data base
A Schema + data are available in share/sql/rest_api.sql

Then Open src/Config/DB.php
Edit getConfig function for *user*, *pwd* and *dsn* with an user granted for the created datbase

Sample:
$default = array(
            'user'      => 'root',
            'pwd'       => '',
            'dsn'       => 'mysql:host=localhost;dbname=rest_api;charset=utf8'
        );

Todo : put some ini file ?

**Route**
Open src/Config/Route.php
You can edit or add a route. 

To add a route simply add a new match in the list in the order to proceed

Sample:
->match('POST|PUT', '/task/(\d+)', function ($id) {
                return array(
                    'controller' => 'Task',
                    'action'     => 'editTask',
                    'params'     => array('id' => $id)
                );
            })

First arg is Http Method (PUT, HEAD, GET, POST, DELETE), you can set on or more methode for a route by using "|"
Secund arg is the route Patern for a Regex
Last arg is the return function, each arg must match the patern parentesis, the funciton must return an array with *controller* to use, *action* to call and an optional params list   

Add a controller
------------------
Add a class inside scr/Controller like this :

namespace G4\Api\Controller;

class MyClass extends ControllerAbstract
{
    public function getIndexAction()
    {
        return "Hello World !";
    }
}

Action name format is <MethodNameToLower><Upper capital of Action in Route>Action
getIndeAction is a default and required method.


PHPUnit
------------------

Some simple test are written.
They require a valide databse connection.

Composer
------------------

Composer is used for PHPUnit and PSR-4 autload
but it's can be removed (Api will use App/PSR-4 autoload)
