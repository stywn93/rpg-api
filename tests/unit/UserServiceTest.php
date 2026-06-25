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
            ->onlyMethods(['searchPaginated', 'getPaginationMeta'])
            ->getMock();

        $userModel->expects($this->once())
            ->method('searchPaginated')
            ->with(15, 2, 'admin', 'active', null)
            ->willReturn([['id' => 1]]);

        $userModel->expects($this->once())
            ->method('getPaginationMeta')
            ->with('default')
            ->willReturn([
                'current_page' => 2,
                'per_page' => 15,
                'total' => 40,
                'last_page' => 3,
            ]);

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $result = $service->list(15, 2, 'admin', 'active');

        $this->assertSame([
            'data' => [['id' => 1]],
            'meta' => [
                'current_page' => 2,
                'per_page' => 15,
                'total' => 40,
                'last_page' => 3,
            ],
        ], $result);
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
            ->onlyMethods(['insert', 'getInsertID', 'find', 'first', 'withDeleted'])
            ->addMethods(['where'])
            ->getMock();

        $plainPassword = 'secret123';

        $userModel->method('withDeleted')->willReturn($userModel);
        $userModel->method('where')->willReturn($userModel);
        $userModel->method('first')->willReturn(null);

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
            'alamat' => 'Jl. Melati No. 10',
            'password' => $plainPassword,
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->assertSame(['id' => 7], $result);
    }

    public function testCreateMapsPeranToRole(): void
    {
        $service = new UserService();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['insert', 'getInsertID', 'find', 'first', 'withDeleted'])
            ->addMethods(['where'])
            ->getMock();

        $userModel->method('withDeleted')->willReturn($userModel);
        $userModel->method('where')->willReturn($userModel);
        $userModel->method('first')->willReturn(null);

        $userModel->expects($this->once())
            ->method('insert')
            ->with($this->callback(function ($data) {
                return ($data['role'] ?? null) === 'user' && ! isset($data['peran']);
            }))
            ->willReturn(true);

        $userModel->expects($this->once())
            ->method('getInsertID')
            ->willReturn(8);

        $userModel->expects($this->once())
            ->method('find')
            ->with(8)
            ->willReturn(['id' => 8, 'role' => 'user']);

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $result = $service->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'secret123',
            'peran' => 'user',
            'status' => 'active',
        ]);

        $this->assertSame(['id' => 8, 'role' => 'user', 'peran' => 'user'], $result);
    }

    public function testCreateReturnsErrorWhenInsertFails(): void
    {
        $service = new UserService();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['insert', 'errors', 'getInsertID', 'find', 'first', 'withDeleted'])
            ->addMethods(['where'])
            ->getMock();

        $plainPassword = 'secret123';
        $validationErrors = ['email' => 'Email already exists'];

        $userModel->method('withDeleted')->willReturn($userModel);
        $userModel->method('where')->willReturn($userModel);
        $userModel->method('first')->willReturn(null);

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
            'alamat' => 'Jl. Kenanga No. 5',
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

        $userModel->expects($this->exactly(2))
            ->method('find')
            ->with(5)
            ->willReturnOnConsecutiveCalls(['id' => 5], ['id' => 5]);

        $userModel->expects($this->once())
            ->method('update')
            ->with(5, $this->callback(function ($data) use ($plainPassword) {
                return isset($data['password'])
                    && $data['password'] !== $plainPassword
                    && password_verify($plainPassword, $data['password']);
            }))
            ->willReturn(true);

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

        $userModel->expects($this->exactly(2))
            ->method('find')
            ->with(5)
            ->willReturnOnConsecutiveCalls(['id' => 5], ['id' => 5]);

        $userModel->expects($this->once())
            ->method('update')
            ->with(5, ['name' => 'Updated'])
            ->willReturn(true);

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $result = $service->update(5, ['name' => 'Updated']);

        $this->assertSame(['id' => 5], $result);
    }

    public function testUpdatePassesAlamatWhenProvided(): void
    {
        $service = new UserService();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['update', 'find'])
            ->getMock();

        $userModel->expects($this->exactly(2))
            ->method('find')
            ->with(5)
            ->willReturnOnConsecutiveCalls(
                ['id' => 5, 'alamat' => 'Jl. Lama'],
                ['id' => 5, 'alamat' => 'Jl. Baru No. 8']
            );

        $userModel->expects($this->once())
            ->method('update')
            ->with(5, ['alamat' => 'Jl. Baru No. 8'])
            ->willReturn(true);

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $result = $service->update(5, ['alamat' => 'Jl. Baru No. 8']);

        $this->assertSame(['id' => 5, 'alamat' => 'Jl. Baru No. 8'], $result);
    }

    public function testDeleteCallsModelDelete(): void
    {
        $service = new UserService();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find', 'delete'])
            ->getMock();

        $userModel->expects($this->once())
            ->method('find')
            ->with(9)
            ->willReturn(['id' => 9]);

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

        $userModel->expects($this->once())
            ->method('find')
            ->with('abc')
            ->willReturn(null);

        $userModel->expects($this->never())
            ->method('update');

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $result = $service->activate('abc');

        $this->assertSame([
            'error' => 'User not found',
            'code' => 404,
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
            ->onlyMethods(['find', 'update'])
            ->getMock();

        $userModel->expects($this->exactly(2))
            ->method('find')
            ->with(4)
            ->willReturnOnConsecutiveCalls(
                ['id' => 4, 'status' => 'active'],
                ['id' => 4, 'status' => 'suspended']
            );

        $userModel->expects($this->once())
            ->method('update')
            ->with(4, ['status' => 'suspended'])
            ->willReturn(true);

        $this->setPrivateProperty($service, 'userModel', $userModel);

        $result = $service->suspend(4);

        $this->assertSame([
            'success' => true,
            'data' => ['id' => 4, 'status' => 'suspended'],
        ], $result);
    }
}
