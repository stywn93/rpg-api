<?php

use App\Services\AuthService;
use App\Models\UserModel;
use App\Libraries\JwtLibrary;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AuthServiceTest extends CIUnitTestCase
{
    public function testRegisterReturnsErrorWhenEmailExists(): void
    {
        $service = new AuthService();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['first'])
            ->addMethods(['where'])
            ->getMock();

        $userModel->method('where')->willReturn($userModel);
        $userModel->method('first')->willReturn(['id' => 1]);

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $result = $service->register([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'secret123',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->assertSame(['error' => 'Email already exists'], $result);
    }

    public function testRegisterHashesPasswordAndInserts(): void
    {
        $service = new AuthService();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['first', 'insert'])
            ->addMethods(['where'])
            ->getMock();

        $userModel->method('where')->willReturn($userModel);
        $userModel->method('first')->willReturn(null);

        $plainPassword = 'secret123';
        $userModel->expects($this->once())
            ->method('insert')
            ->with($this->callback(function ($data) use ($plainPassword) {
                return isset($data['password'])
                    && $data['password'] !== $plainPassword
                    && password_verify($plainPassword, $data['password']);
            }))
            ->willReturn(1);

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $result = $service->register([
            'name' => 'Test User',
            'email' => 'new@example.com',
            'password' => $plainPassword,
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->assertSame(['success' => true], $result);
    }

    public function testLoginReturnsErrorWhenUserNotFound(): void
    {
        $service = new AuthService();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['first'])
            ->addMethods(['where'])
            ->getMock();

        $userModel->method('where')->willReturn($userModel);
        $userModel->method('first')->willReturn(null);

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $result = $service->login('missing@example.com', 'secret123');

        $this->assertSame(['error' => 'User not found'], $result);
    }

    public function testLoginReturnsErrorWhenUserSuspended(): void
    {
        $service = new AuthService();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['first'])
            ->addMethods(['where'])
            ->getMock();

        $userModel->method('where')->willReturn($userModel);
        $userModel->method('first')->willReturn([
            'id' => 1,
            'email' => 'user@example.com',
            'role' => 'admin',
            'status' => 'suspended',
            'password' => password_hash('secret123', PASSWORD_DEFAULT),
        ]);

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $result = $service->login('user@example.com', 'secret123');

        $this->assertSame(['error' => 'User suspended'], $result);
    }

    public function testLoginReturnsErrorWhenPasswordInvalid(): void
    {
        $service = new AuthService();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['first'])
            ->addMethods(['where'])
            ->getMock();

        $userModel->method('where')->willReturn($userModel);
        $userModel->method('first')->willReturn([
            'id' => 1,
            'email' => 'user@example.com',
            'role' => 'admin',
            'status' => 'active',
            'password' => password_hash('secret123', PASSWORD_DEFAULT),
        ]);

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $result = $service->login('user@example.com', 'wrong');

        $this->assertSame(['error' => 'Invalid password'], $result);
    }

    public function testLoginReturnsTokenWhenValid(): void
    {
        $service = new AuthService();

        $user = [
            'id' => 1,
            'email' => 'user@example.com',
            'role' => 'admin',
            'status' => 'active',
            'password' => password_hash('secret123', PASSWORD_DEFAULT),
        ];

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['first'])
            ->addMethods(['where'])
            ->getMock();

        $userModel->method('where')->willReturn($userModel);
        $userModel->method('first')->willReturn($user);

        $jwt = $this->getMockBuilder(JwtLibrary::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['generateToken'])
            ->getMock();

        $jwt->expects($this->once())
            ->method('generateToken')
            ->with($user)
            ->willReturn('test-token');

        $this->setPrivateProperty($service, 'userModel', $userModel);
        $this->setPrivateProperty($service, 'jwt', $jwt);

        $result = $service->login('user@example.com', 'secret123');

        $this->assertSame([
            'token' => 'test-token',
            'expires_in' => 7200,
        ], $result);
    }
}
