<?php

namespace App\Models;

use App\Models\User;
use App\Models\Project;
use App\Models\TicketDetail;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'title',
        'description',
        'attachment_path',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detail()
    {
        return $this->hasOne(TicketDetail::class);
    }
}
