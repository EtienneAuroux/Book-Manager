<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents a book in the database.
 * Each book has a title and an author.
 * 
 * @property int $id
 * @property string $title
 * @property string $author
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereTitle(string $title)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereAuthor(string $author)
 */
class Book extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'author',
    ];
}
