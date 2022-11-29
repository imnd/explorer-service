<?php

namespace App\Models;

use App\Elastic\FileConfigurator;
use Illuminate\Database\Eloquent\Model;
use ScoutElastic\Searchable;

/**
 * App\Models\File
 *
 * @property int $id
 * @property int|null $parent_id
 * @property string $name
 * @property string $extension
 * @property string $type
 * @property string|null $external_id
 * @property string $uuid
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\File|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\File whereUuid($value)
 * @mixin \Eloquent
 */
class File extends Model
{
    use Searchable;

    const TYPE_FOLDER = 'folder';
    const TYPE_S3 = 'FileS3';
    const TYPE_D24 = 'FileD24';
    const TYPES = [
        self::TYPE_FOLDER,
        self::TYPE_S3,
        self::TYPE_D24,
    ];

    protected $indexConfigurator = FileConfigurator::class;

    protected $mapping = [
        'properties' => [
            'name' => [
                'type' => 'text',
                'analyzer' => 'file_russian',
                'search_analyzer' => 'file_russian_search',
            ],
            'type' => [
                'type' => 'keyword',
            ],
        ]
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    protected $table = 'files';

    protected $fillable = [
        'parent_id', 'name', 'id', 'type', 'external_id', 'uuid', 'user_id', 'payload', 'extension', 'favorite'
    ];

    /**
     * Костыль для эластика 6.*. иначе тесты не проходят, выдает:
     * Failed to parse value[1] as only [true] or [false] are allowed
     */
    public static function boot()
    {
        parent::boot();

        self::updating(function($model) {
            $model->favorite = (bool)$model->favorite;
        });
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public static function getTypesList()
    {
        return implode(',', self::TYPES);
    }
}
