<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TranslationKey extends Model
{
    protected $fillable = ['group', 'key'];

    public function texts(): HasMany
    {
        return $this->hasMany(TranslationText::class);
    }
}
