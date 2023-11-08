<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OAuthToken extends Model
{
    protected $table = 'oauth_tokens';

    protected $fillable = [
        'user_id',
        'provider',
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
