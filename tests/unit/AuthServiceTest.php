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
            'alamat' => 'Jl. Anggrek No. 1',
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
            ->onlyMethods(['first', 'insert', 'find', 'getInsertID'])
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

        $userModel->expects($this->once())
            ->method('getInsertID')
            ->willReturn(9);

        $userModel->expects($this->once())
            ->method('find')
            ->with(9)
            ->willReturn([
                'id' => 9,
                'name' => 'Test User',
                'email' => 'new@example.com',
                'alamat' => 'Jl. Mawar No. 2',
                'role' => 'admin',
                'status' => 'active',
                'password' => 'hashed-password',
            ]);

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $result = $service->register([
            'name' => 'Test User',
            'email' => 'new@example.com',
            'alamat' => 'Jl. Mawar No. 2',
            'password' => $plainPassword,
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->assertSame([
            'id' => 9,
            'name' => 'Test User',
            'email' => 'new@example.com',
            'alamat' => 'Jl. Mawar No. 2',
            'role' => 'admin',
            'status' => 'active',
        ], $result);
    }

    public function testRegisterMapsPeranToRoleAndDefaultsToUser(): void
    {
        $service = new AuthService();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['first', 'insert', 'find', 'getInsertID'])
            ->addMethods(['where'])
            ->getMock();

        $userModel->method('where')->willReturn($userModel);
        $userModel->method('first')->willReturn(null);

        $plainPassword = 'secret123';
        $userModel->expects($this->exactly(2))
            ->method('insert')
            ->with($this->callback(function ($data) use ($plainPassword) {
                return isset($data['role'], $data['password'])
                    && $data['role'] === 'user'
                    && $data['password'] !== $plainPassword
                    && password_verify($plainPassword, $data['password']);
            }))
            ->willReturn(1);

        $userModel->expects($this->exactly(2))
            ->method('getInsertID')
            ->willReturn(10);

        $userModel->expects($this->exactly(2))
            ->method('find')
            ->with(10)
            ->willReturn([
                'id' => 10,
                'name' => 'Regular User',
                'email' => 'user@example.com',
                'role' => 'user',
                'status' => 'active',
                'password' => 'hashed-password',
            ]);

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $resultWithPeran = $service->register([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => $plainPassword,
            'peran' => 'user',
            'status' => 'active',
        ]);

        $resultWithDefault = $service->register([
            'name' => 'Regular User',
            'email' => 'user2@example.com',
            'password' => $plainPassword,
            'status' => 'active',
        ]);

        $this->assertSame('user', $resultWithPeran['role']);
        $this->assertSame('user', $resultWithDefault['role']);
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
            'alamat' => 'Jl. Sudirman No. 3',
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
            'alamat' => 'Jl. Thamrin No. 4',
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
            'name' => 'Test User',
            'email' => 'user@example.com',
            'phone' => '08123456789',
            'alamat' => 'Jl. Merdeka No. 5',
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
            'id' => 1,
            'name' => 'Test User',
            'email' => 'user@example.com',
            'phone' => '08123456789',
            'alamat' => 'Jl. Merdeka No. 5',
            'peran' => 'admin',
            'role' => 'admin',
            'status' => 'active',
            'expires_in' => JwtLibrary::TOKEN_TTL_SECONDS,
        ], $result);
    }
}
