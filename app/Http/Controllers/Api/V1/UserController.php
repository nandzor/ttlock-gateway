<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController {
    protected $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request) {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);

        // Using new unified getPaginate method from BaseService
        $users = $this->userService->getPaginate($search, $perPage);

        return $this->paginatedResponse($users, 'Users retrieved successfully');
    }

    /**
     * Get pagination options
     */
    public function paginationOptions() {
        $options = $this->userService->getPerPageOptions();

        return $this->successResponse($options, 'Pagination options retrieved successfully');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,user',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = $this->userService->createUser($request->all());

        return $this->createdResponse($user, 'User created successfully');
    }

    /**
     * Display the specified user
     */
    public function show($id) {
        $user = $this->userService->findById($id);

        if (!$user) {
            return $this->notFoundResponse('User not found');
        }

        return $this->successResponse($user, 'User retrieved successfully');
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id) {
        $user = $this->userService->findById($id);

        if (!$user) {
            return $this->notFoundResponse('User not found');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,user',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $this->userService->updateUser($user, $request->all());

        return $this->successResponse($user->fresh(), 'User updated successfully');
    }

    /**
     * Remove the specified user
     */
    public function destroy($id) {
        $user = $this->userService->findById($id);

        if (!$user) {
            return $this->notFoundResponse('User not found');
        }

        $this->userService->deleteUser($user);

        return $this->successResponse(null, 'User deleted successfully');
    }
}
