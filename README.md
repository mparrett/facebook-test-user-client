#Facebook Test User Client

This tool provides a wrapper for the Facebook Test User API. With it you can 
read, create, update and delete the test users of your application. You can 
also create friendships between your test users.

While some knowledge of the underlying API is required to use it (notably which 
optional parameters to pass to the CreateUser method and the work flow required 
to create friendships) it is envisioned that this client would be used in your 
own testing frameworks to create higher level interactions. 

##Installation

The client is installed via composer. 

Add the following to your composer.json file.

````json 
{
    "require": {
        "bwaine/FacebookTestUserClient": "dev-master"
    }
}
````

Run *php composer.phar install* or *php composer.phar update*.

##Usage

Actions are performed against the Facebook Test user API by creating an instance
of the client and using it to create 'commands'. Commands are then executed 
and the responses automatically parsed into something which resembles a php array.

````php 
<?php 

// include the clients namespace and alias it to Client.
use Bwaine\FacebookTestUserClient\Client;

// Create a client object
$client = Client::factory();

// Get the command you wish to use.

// In this example we use the client to obtain an 'app access_token'. This is
// then used in subsequent requests.  
// When getting a command the name of the command plus any arguments are passed
// to the clients 'getCommand' method. 

$authCommand = $this->object->getCommand('ObtainAppAccessToken', 
                array(
                 "client_id" => $yourAppId,
                 "client_secret" => $yourAppId)
                );

// Execute the command and save the response in a variable.

try {

    $response = $authCommand->execute();

} catch (\Exception $e) {
    // Bad / failed requests throw an exception. Handle this here or let it 
    // bubble up to your top level error handler. 
    throw $e;
}

````

##Commands

All commands require arguments to be passed to them when calling the clients 
'getCommand'method. These are outlined below. 

**NOTE: Where a command requires an app_access_token you must obtain one 
by using the *ObtainAppAccessToken* command**

###ObtainAppAccessToken

This method allows you to exchange your app's id and secret for an app access 
token. The token is used on subsequent requests to create and manipulate test 
users on behalf of your application.

#### Params

| Name          | Explanation                                               | Required | 
| client_id     | Your application ID (obtained from the developer app)     | Y        |
| client_secret | Your application secret (obtained from the developer app) | Y        | 

#### Response

| Name          | Explanation                                                 | 
| access_token  | Used to make further calls to the Api on behalf of your app | 

````php

        $appId = "YOUR ID GOES HERE";
        $appSecret = "YOUR SECRET GOES HERE";

        $client = Client::factory();

        $authCommand = $client->getCommand('ObtainAppAccessToken', array(
            "client_id" => $appId,
            "client_secret" => $appSecret));

        try {
            $response = $authCommand->execute();
        } catch (\Exception $e) {
            // Handle failed requests
        }
        
        // Save for future requests!
        $token = $response['access_token']
````

###CreateUser

This method creates a test user according to your specification.

#### Params

| Name         | Explanation                                              | Required | 
| appId        | Your application ID (obtained from the developer app)    | Y        |
| access_token | An app access_token (see ObtainAppAccessToken)           | Y        |
| name         | The users name                                           | n        |
| permissions  | Comma seperated list of permissions the user has granted | n        |
| installed    | True / False - Has the user installed your app           | n        |
| locale       | The users locale                                         | n        |

#### Response

| Name         | Explanation                                                       | 
| id           | The users id                                                      | 
| access_token | A user access_token used to make Open Graph requests for the user | 
| login_url    | A url to allowing you to login as the user                        | 
| email        | The users email address                                           | 
| password     | The users password                                                | 

````php
        
        // Access token from previous request. See Above.
        $token = $response['access_token'];
        $appId = "YOUR ID GOES HERE";

        $client = Client::factory();

        $command = $client>getCommand('CreateUser', array(
            "appId" => $this->appId,
            "access_token" => token,
            ));

        try {
            $user = $command->execute();
        } catch (\Exception $e) {
            // Handle failure here
        }

        $id = $user['id'];

````

###DeleteUser

This method deletes a previously created test user.

#### Params

| Name         | Explanation                                              | Required | 
| testUserId   | An ID of a test user associated with your application    | Y        |
| access_token | An app access_token (see ObtainAppAccessToken)           | Y        |

#### Response

| Name         | Explanation            | 
| result       | Success (true / false) | 


````php
        
        // Access token from previous request. See Above.
        $token = $response['access_token'];
        $appId = "YOUR ID GOES HERE";

        $client = Client::factory();

        // Delete the user.
        $deleteCommand = $client->getCommand('DeleteUser', array(
            'testUserId' => $testUserId,
            'access_token' => $token
                ));

        try {
            $response = $deleteCommand->execute();
        } catch (\Exception $e) {
            // Handle failure here.
        }
        
        $status = $response['result'];

````

###GetUsers

Gets all the test users associated with your application. Complete with 
access tokens and other data.

#### Params

| Name         | Explanation                                              | Required | 
| appId        | Your application ID (obtained from the developer app)    | Y        |
| access_token | An app access_token (see ObtainAppAccessToken)           | Y        |

#### Response

| Name         | Explanation        | 
| users        | And array of users | 

#### Response (['users'] - )

| Name         | Explanation                                              | 
| id           | An ID of a test user associated with your application    | 
| access_token | An app access_token (see ObtainAppAccessToken)           |
| login_url    | A url to allowing you to login as the user               | 


````php
        
        // Access token from previous request. See Above.
        $token = $response['access_token'];
        $appId = "YOUR ID GOES HERE";

        $client = Client::factory();

        $command = $client->getCommand('GetUsers', array(
            "appId" => $appId,
            "access_token" => $token));

        try {
            $response = $command->execute();
        } catch (\Exception $e) {
            // Handle failure here.
        }

        foreach ($response['users'] as $user) {
            // Do things with users.
            $id = $user['id']; 
        }

````

###ConnectFriend

This command allows you to friend request another test user. It is also used for 
replying to the request on behalf of the test user.

First a friend request is sent from user1 to user 2 using user1's access_token. 
The the same command is used (with the user ID's reversed) with user2's 
access_token to accept the request. See below for an example.

#### Params

| Name         | Explanation                                              | Required | 
| user1        | A test user id associated to the application             | Y        |
| user2        | A test user id associated to the application             | Y        |
| access_token | user1's access token.                                    | Y        |

**Note: It takes two commands to create a friendship between test users. In the 
first request user1's id is passed first and user1's access_token is used. 
In the second request user2's id is passed first and user2's access_token 
is used.**

#### Response

| Name         | Explanation           | 
| result       | Success: true / false | 


````php
        // Missing Code. Two users are created using the CreateUser command.
        // The result of each command is assigned to $user1 and $user2.

        $client = Client::factory();

        $connectCommand1 = $client->getCommand('ConnectFriend', array(
            "user1" => $user1['id'],
            "user2" => $user2['id'],
            "access_token" => $user1['access_token']
                ));

        $connectCommand2 = $client->getCommand('ConnectFriend', array(
            "user1" => $user2['id'],
            "user2" => $user1['id'],
            "access_token" => $user2['access_token']
                ));

        try {
            $resp1 = $connectCommand1->execute();
            $resp2 = $connectCommand2->execute();
        } catch (\Exception $e) {
            // Handle failure here.
        }
        
        $success = ($resp1['result'] && $resp2['result']);

````
##Tests

There are some integration style tests bundled with the extension. 
They make actual calls to the Facebook api and as such require an appid and
app secret. 

To run the tests copy phpunit.dist.xml to phpunit.xml and then insert your app 
ID and secret into the places indicated. 

````
cp phpunit.dist.xml phpunit.xml
nano phpunit.xml // Edit file
phpunit
````

**NOTE: The tests are destructive. After each test the test users for the 
application are destroyed. If you have test users in your application and need 
to keep them, do not run this suite against your application.**
 