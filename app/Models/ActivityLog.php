<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id','action','model_type','model_id',
        'url','method','ip','user_agent','description','before','after'
    ];

    protected $casts = [
        'before' => 'array',
        'after'  => 'array',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}