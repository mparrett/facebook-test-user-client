<?php

use Bwaine\FacebookTestUserClient\Client;

class ClientTest extends \PHPUnit_Framework_TestCase {

    protected $object;
    protected $appId;
    protected $appSecret;
    protected $access_token;

    public function setUp() {
        
        // Set these constants in the PHPUnit config file.
        $this->appId = FB_APP_ID;
        $this->appSecret = FB_APP_SECRET;

        $this->object = Client::factory();

        $authCommand = $this->object->getCommand('ObtainAppAccessToken', array("client_id" => $this->appId,
            "client_secret" => $this->appSecret));

        try {
            $response = $authCommand->execute();
        } catch (\Exception $e) {
            $this->fail("Facebook Authentication failed. " . $e->getMessage());
        }

        $this->access_token = $response['access_token'];
    }

    public function testCreateUser() {

        $command = $this->object->getCommand('CreateUser', array("appId" => $this->appId,
            "access_token" => $this->access_token,
            "name" => "Ben Waine",
            "permissions" => "read_stream"));

        try {
            $user = $command->execute();
        } catch (\Exception $e) {
            $this->fail('Create user command failed');
        }

        $this->assertNotNull($user['id']);
        $this->assertNotNull($user['email']);
        $this->assertNotNull($user['access_token']);
        $this->assertNotNull($user['login_url']);
        $this->assertNotNull($user['password']);
    }

    public function testGetUsers() {
        $command = $this->object->getCommand('GetUsers', array("appId" => $this->appId,
            "access_token" => $this->access_token));

        try {
            $response = $command->execute();
        } catch (\Exception $e) {
            $this->fail('Get users command failed', null, $e);
        }

        foreach ($response['users'] as $user) {
            $this->assertNotNull($user['id']);
            $this->assertNotNull($user['access_token']);
            $this->assertNotNull($user['login_url']);
        }
    }

    public function testGetUser() {

        // Create user
        $createUsercommand = $this->object->getCommand('CreateUser', array("appId" => $this->appId,
            "access_token" => $this->access_token,
            "name" => "Ben Waine",
            "permissions" => "read_stream"));

        try {
            $response = $createUsercommand->execute();
        } catch (\Exception $e) {
            $this->fail("Failed to create user. " . $e->getMessage());
        }

        $testUserId = $response['id'];

        // Get the user
        $getUserCommand = $this->object->getCommand('GetUser', array("testUserId" => $testUserId,
            "access_token" => $this->access_token));

        try {
            $user = $getUserCommand->execute();
        } catch (\Exception $e) {
            $this->fail("Failed to get user. " . $e->getMessage());
        }

        $this->assertNotNull($user['id']);
        $this->assertNotNull($user['name']);
        $this->assertNotNull($user['first_name']);
        $this->assertNotNull($user['last_name']);
        $this->assertNotNull($user['link']);
        $this->assertNotNull($user['gender']);
        $this->assertNotNull($user['locale']);
    }

    public function testDeleteUser() {

        // Create a user
        $command = $this->object->getCommand('CreateUser', array("appId" => $this->appId,
            "access_token" => $this->access_token,
            "name" => "Ben Waine",
            "permissions" => "read_stream"));

        try {
            $user = $command->execute();
        } catch (\Exception $e) {
            $this->fail('Create user command failed');
        }

        // Delete the user.
        $deleteCommand = $this->object->getCommand('DeleteUser', array(
            'testUserId' => $user['id'],
            'access_token' => $this->access_token
                ));

        try {
            $delResponse = $deleteCommand->execute();
        } catch (\Exception $e) {
            $this->fail("DeleteUser command failed");
        }

        $this->assertTrue($delResponse['result']);
    }

    public function testConnectFriend() {

        // Create a user
        $createCommand1 = $this->object->getCommand('CreateUser', array("appId" => $this->appId,
            "access_token" => $this->access_token,
            "name" => "Ben Waine",
            "permissions" => "read_stream"));

        try {
            $user1 = $createCommand1->execute();
        } catch (\Exception $e) {
            $this->fail('Create user command failed (1st User)');
        }

        // Create a user
        $createCommand2 = $this->object->getCommand('CreateUser', array("appId" => $this->appId,
            "access_token" => $this->access_token,
            "name" => "Bryan Rigby",
            "permissions" => "read_stream"));

        try {
            $user2 = $createCommand2->execute();
        } catch (\Exception $e) {
            $this->fail('Create user command failed (2nd user)');
        }

        // Make them fweinds
        $connectCommand1 = $this->object->getCommand('ConnectFriend', array(
            "user1" => $user1['id'],
            "user2" => $user2['id'],
            "access_token" => $user1['access_token']
                ));

        $connectCommand2 = $this->object->getCommand('ConnectFriend', array(
            "user1" => $user2['id'],
            "user2" => $user1['id'],
            "access_token" => $user2['access_token']
                ));

        try {
            $resp1 = $connectCommand1->execute();
            $resp2 = $connectCommand2->execute();
        } catch (\Exception $e) {
            $this->fail("FriendConnect failed: " . $e->getMessage());
        }
        
        $this->assertTrue($resp1['result']);
        $this->assertTrue($resp2['result']);
        
    }

    protected function tearDown() {

        $command = $this->object->getCommand('GetUsers', array("appId" => $this->appId,
            "access_token" => $this->access_token));

        try {
            $response = $command->execute();
        } catch (\Exception $e) {
            $this->fail("Tear down failed. Could not get users to delete: " . $e->getMessage());
        }

        if (!empty($response['users'])) {

            foreach ($response['users'] as $user) {

                $deleteCommand = $this->object->getCommand('DeleteUser', array(
                    'testUserId' => $user['id'],
                    'access_token' => $this->access_token
                        ));

                try {
                    $deleteCommand->execute();
                } catch (\Exception $e) {
                    $this->fail("Tear down failed. Failed to delete user: " . $e->getMessage());
                }
            }
        }
    }

}
