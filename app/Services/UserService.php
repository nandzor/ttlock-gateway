<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserService extends BaseService {
    /**
     * Constructor
     */
    public function __construct() {
        $this->model = new User();
        $this->searchableFields = ['name', 'email'];
    }

    /**
     * Find user by ID
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User {
        /** @var User|null */
        return parent::findById($id);
    }

    /**
     * Create new user
     */
    public function createUser(array $data): User {
        $data['password'] = Hash::make($data['password']);

        $user = $this->create($data);

        /** @var User $user */
        return $user;
    }

    /**
     * Update user
     */
    public function updateUser(Model $user, array $data): bool {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $this->update($user, $data);
    }

    /**
     * Delete user
     */
    public function deleteUser(Model $user): bool {
        return $this->delete($user);
    }
}
