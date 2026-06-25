<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'name',
        'email',
        'phone',
        'alamat',
        'password',
        'role',
        'status'
    ];

    // Timestamp
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation (opsional tapi recommended)
    protected $validationRules = [
        'name' => 'required|min_length[3]',
        'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
        'alamat' => 'permit_empty|string',
        'password' => 'required|min_length[6]',
        'role' => 'permit_empty|in_list[admin,user]',

    ];

    protected $validationMessages = [];
    protected $skipValidation = false;

    public function searchPaginated(int $perPage = 10, ?int $page = null, ?string $searchTerm = null, ?string $status = null, ?string $role = null): array
    {
        $searchTerm = is_string($searchTerm) ? trim($searchTerm) : '';
        $status = is_string($status) ? trim($status) : '';
        $role = is_string($role) ? trim($role) : '';

        if ($status !== '') {
            $this->where('status', $status);
        }

        if ($role !== '') {
            $this->where('role', $role);
        }

        if ($searchTerm !== '') {
            $this->groupStart()
                ->like('name', $searchTerm)
                ->orLike('email', $searchTerm)
                ->orLike('phone', $searchTerm)
                ->orLike('alamat', $searchTerm)
                ->orLike('role', $searchTerm)
                ->orLike('status', $searchTerm)
                ->groupEnd();
        }

        return $this->paginate($perPage, 'default', $page);
    }

    public function getPaginationMeta(string $group = 'default'): array
    {
        if ($this->pager === null) {
            return [
                'current_page' => 1,
                'per_page' => 0,
                'total' => 0,
                'last_page' => 1,
            ];
        }

        return [
            'current_page' => $this->pager->getCurrentPage($group),
            'per_page' => $this->pager->getPerPage($group),
            'total' => $this->pager->getTotal($group),
            'last_page' => $this->pager->getPageCount($group),
        ];
    }
}
