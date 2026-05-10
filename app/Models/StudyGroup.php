<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class StudyGroup extends Model
{
    protected $fillable = [
        'name',
        'subject',
        'emoji',
        'invite_code',
        'school_id',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $group) {
            if (empty($group->invite_code)) {
                do {
                    $code = strtoupper(Str::random(10));
                } while (self::where('invite_code', $code)->exists());

                $group->invite_code = $code;
            }
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'study_group_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(StudyGroupMessage::class);
    }

    /** Whether this group was created/owned by a school (not an individual student). */
    public function isSchoolGroup(): bool
    {
        return $this->school_id !== null;
    }
}
