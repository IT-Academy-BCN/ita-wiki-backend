<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected string $guard_name = 'api';

    protected $fillable = [
        'name',
        'email',
        'password',
        'github_id',
        'github_user_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Find user by GitHub ID
     */
    public static function findByGithubId(int $githubId): ?self
    {
        return static::where('github_id', $githubId)->first();
    }

    /**
     * Get user's role name for API responses
     */
    public function getRoleName(): string
    {
        return $this->getRoleNames()->first() ?? 'anonymous';
    }

    
    protected function getDefaultGuardName(): string 
    { 
        return $this->guard_name; 
    }
}
