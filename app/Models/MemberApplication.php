<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\Discipline;
use Database\Factories\MemberApplicationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A designer asking to join the collective through Studio > Join.
 */
#[Fillable(['name', 'email', 'phone', 'city', 'discipline', 'portfolio_url', 'instagram', 'message'])]
class MemberApplication extends Model
{
    /** @use HasFactory<MemberApplicationFactory> */
    use HasFactory;

    /**
     * New applications wait for review.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discipline' => Discipline::class,
            'status' => ApplicationStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Record an admin's decision.
     */
    public function review(ApplicationStatus $status): void
    {
        $this->forceFill(['status' => $status, 'reviewed_at' => now()])->save();
    }

    /**
     * @param  Builder<MemberApplication>  $query
     */
    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->where('status', ApplicationStatus::Pending);
    }
}
