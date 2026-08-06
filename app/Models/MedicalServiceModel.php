<?php

namespace App\Models;

use CodeIgniter\Model;

class MedicalServiceModel extends Model
{
    protected $table            = 'medical_services';
    protected $primaryKey       = 'service_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'service_name',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'service_name' => 'required|min_length[2]|max_length[100]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
}
