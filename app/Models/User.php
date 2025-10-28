<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable {
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool {
        return $this->role === 'admin';
    }

    /**
     * Check if user is operator
     */
    public function isOperator(): bool {
        return $this->role === 'operator';
    }

    /**
     * Check if user is viewer
     */
    public function isViewer(): bool {
        return $this->role === 'viewer';
    }

    /**
     * Get API credentials created by this user
     */
    public function apiCredentials() {
        return $this->hasMany(ApiCredential::class, 'created_by');
    }

    /**
     * Get CCTV layouts created by this user
     */
    public function cctvLayouts() {
        return $this->hasMany(CctvLayoutSetting::class, 'created_by');
    }

    /**
     * Get files uploaded by this user
     */
    public function uploadedFiles() {
        return $this->hasMany(StorageFile::class, 'uploaded_by');
    }

    /**
     * Scope: Admin users
     */
    public function scopeAdmins($query) {
        return $query->where('role', 'admin');
    }

    /**
     * Scope: Operator users
     */
    public function scopeOperators($query) {
        return $query->where('role', 'operator');
    }

    /**
     * Scope: Viewer users
     */
    public function scopeViewers($query) {
        return $query->where('role', 'viewer');
    }
}
