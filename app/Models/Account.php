<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $table = 'accounts';
    public $timestamps=false;
    protected $fillable=[
            'email'
            ,'oauth_token'
            ,'refresh_token'
            ,'token_expires_at'
            ,'clientId',
            'clientSecret',
            'tenant_id',
            'redirectUri' ,
            'urlAuthorize',
            'urlAccessToken' ,
            'urlResourceOwnerDetails',
            'scopes',
    ];
}
