<?php

namespace App\Models;

use App\Models\Filters\IsNullFilter;
use App\Models\Filters\TaskOverdueFilter;
use App\Models\Filters\WhereInFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Lacodix\LaravelModelFilter\Traits\HasFilters;
use Lacodix\LaravelModelFilter\Traits\IsSearchable;
use LaravelArchivable\Archivable;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Task extends Model implements Sortable
{
    use Archivable, HasFactory, HasFilters, IsSearchable, SortableTrait;

    protected $fillable = [
        'project_id',
        'group_id',
        'created_by_user_id',
        'assigned_to_user_id',
        'name',
        'number',
        'description',
        'due_on',
        'estimation',
        'hidden_from_clients',
        'billable',
        'order_column',
        'completed_at',
    ];

    protected $searchable = [
        'name',
        'number',
    ];

    protected $casts = [
        'due_on' => 'date',
        'completed_at' => 'datetime',
        'hidden_from_clients' => 'boolean',
        'billable' => 'boolean',
    ];

    public function filters(): Collection
    {
        return collect([
            (new WhereInFilter('group_id'))->setQueryName('groups'),
            (new WhereInFilter('assigned_to_user_id'))->setQueryName('assignees'),
            (new TaskOverdueFilter('due_on'))->setQueryName('overdue'),
            (new IsNullFilter('due_on'))->setQueryName('not_set'),
        ]);
    }

    protected static function booted(): void
    {
        static::addGlobalScope('ordered', function ($query) {
            $query->ordered();
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function taskGroup(): BelongsTo
    {
        return $this->belongsTo(TaskGroup::class, 'group_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }
}
