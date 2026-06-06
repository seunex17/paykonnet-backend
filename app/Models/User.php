<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Climactic\Credits\Traits\HasCredits;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasCredits, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'uuid',
        'picture',
        'firstname',
        'lastname',
        'is_active',
        'lockscreen',
        'transfer_pin',
        'id_type',
        'id_number',
        'id_verified',
        'agent_level',
        'next_agent_payment_date',
        'mono_id',
        'mono_mandate',
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
            'is_active' => 'boolean',
            'id_verified' => 'boolean',
            'next_agent_payment_date' => 'date',
        ];
    }

    /**
     * Get the wallets for the user.
     */
    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }
}
