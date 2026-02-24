<?php

use App\Services\UserService;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class UserServiceTest extends CIUnitTestCase
{
    public function testListUsesPaginate(): void
    {
        $service = new UserService();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['paginate'])
            ->getMock();

        $userModel->expects($this->once())
            ->method('paginate')
            ->with(15)
            ->willReturn([['id' => 1]]);

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $result = $service->list(15);

        $this->assertSame([['id' => 1]], $result);
    }

    public function testFindReturnsUser(): void
    {
        $service = new UserService();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find'])
            ->getMock();

        $userModel->expects($this->once())
            ->method('find')
            ->with(10)
            ->willReturn(['id' => 10]);

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $result = $service->find(10);

        $this->assertSame(['id' => 10], $result);
    }

    public function testCreateHashesPasswordAndReturnsInsertedUser(): void
    {
        $service = new UserService();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['insert', 'getInsertID', 'find'])
            ->getMock();

        $plainPassword = 'secret123';

        $userModel->expects($this->once())
            ->method('insert')
            ->with($this->callback(function ($data) use ($plainPassword) {
                return isset($data['password'])
                    && $data['password'] !== $plainPassword
                    && password_verify($plainPassword, $data['password']);
            }))
            ->willReturn(true);

        $userModel->expects($this->once())
            ->method('getInsertID')
            ->willReturn(7);

        $userModel->expects($this->once())
            ->method('find')
            ->with(7)
            ->willReturn(['id' => 7]);

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $result = $service->create([
            'name' => 'Test User',
            'email' => 'new@example.com',
            'password' => $plainPassword,
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->assertSame(['id' => 7], $result);
    }

    public function testCreateReturnsErrorWhenInsertFails(): void
    {
        $service = new UserService();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['insert', 'errors', 'getInsertID', 'find'])
            ->getMock();

        $plainPassword = 'secret123';
        $validationErrors = ['email' => 'Email already exists'];

        $userModel->expects($this->once())
            ->method('insert')
            ->with($this->callback(function ($data) use ($plainPassword) {
                return isset($data['password'])
                    && $data['password'] !== $plainPassword
                    && password_verify($plainPassword, $data['password']);
            }))
            ->willReturn(false);

        $userModel->expects($this->once())
            ->method('errors')
            ->willReturn($validationErrors);

        $userModel->expects($this->never())
            ->method('getInsertID');

        $userModel->expects($this->never())
            ->method('find');

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $result = $service->create([
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => $plainPassword,
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->assertSame(['error' => $validationErrors], $result);
    }

    public function testUpdateHashesPasswordWhenProvided(): void
    {
        $service = new UserService();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['update', 'find'])
            ->getMock();

        $plainPassword = 'secret123';

        $userModel->expects($this->once())
            ->method('update')
            ->with(5, $this->callback(function ($data) use ($plainPassword) {
                return isset($data['password'])
                    && $data['password'] !== $plainPassword
                    && password_verify($plainPassword, $data['password']);
            }))
            ->willReturn(true);

        $userModel->expects($this->once())
            ->method('find')
            ->with(5)
            ->willReturn(['id' => 5]);

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $result = $service->update(5, ['password' => $plainPassword]);

        $this->assertSame(['id' => 5], $result);
    }

    public function testUpdateDoesNotHashWhenPasswordNotProvided(): void
    {
        $service = new UserService();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['update', 'find'])
            ->getMock();

        $userModel->expects($this->once())
            ->method('update')
            ->with(5, ['name' => 'Updated'])
            ->willReturn(true);

        $userModel->expects($this->once())
            ->method('find')
            ->with(5)
            ->willReturn(['id' => 5]);

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $result = $service->update(5, ['name' => 'Updated']);

        $this->assertSame(['id' => 5], $result);
    }

    public function testDeleteCallsModelDelete(): void
    {
        $service = new UserService();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['delete'])
            ->getMock();

        $userModel->expects($this->once())
            ->method('delete')
            ->with(9)
            ->willReturn(true);

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $service->delete(9);
        $this->assertTrue(true);
    }

    public function testActivateReturnsErrorWhenIdInvalid(): void
    {
        $service = new UserService();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find', 'update'])
            ->getMock();

        $userModel->expects($this->never())
            ->method('find');

        $userModel->expects($this->never())
            ->method('update')
            ->with(3, ['status' => 'active']);

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $result = $service->activate('abc');

        $this->assertSame([
            'error' => 'ID not valid',
            'code' => 400,
        ], $result);
    }

    public function testActivateReturnsErrorWhenUserNotFound(): void
    {
        $service = new UserService();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find', 'update'])
            ->getMock();

        $userModel->expects($this->once())
            ->method('find')
            ->with(3)
            ->willReturn(null);

        $userModel->expects($this->never())
            ->method('update');

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $result = $service->activate(3);

        $this->assertSame([
            'error' => 'User not found',
            'code' => 404,
        ], $result);
    }

    public function testActivateUpdatesStatusToActive(): void
    {
        $service = new UserService();

        $existingUser = ['id' => 3, 'status' => 'suspended'];
        $updatedUser = ['id' => 3, 'status' => 'active'];

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find', 'update'])
            ->getMock();

        $userModel->expects($this->exactly(2))
            ->method('find')
            ->with(3)
            ->willReturnOnConsecutiveCalls($existingUser, $updatedUser);

        $userModel->expects($this->once())
            ->method('update')
            ->with(3, ['status' => 'active'])
            ->willReturn(true);

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $result = $service->activate(3);

        $this->assertSame([
            'success' => true,
            'data' => $updatedUser,
        ], $result);
    }

    public function testSuspendUpdatesStatusToSuspended(): void
    {
        $service = new UserService();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['update'])
            ->getMock();

        $userModel->expects($this->once())
            ->method('update')
            ->with(4, ['status' => 'suspended'])
            ->willReturn(true);

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $service->suspend(4);
        $this->assertTrue(true);
    }
}
