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
     * This allows mass-assignment when creating or updating a Book object.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'author',
    ];

    // Laravel automatically assumes that every model has an ID primary key.
    // That's actually done during migration since it calls $table->id().
    // That means I can do Book::find(int) and $book->id.

    // Apparently there is a convention in Eloquent such
    // that if my database names is the plural snake case form of my model 
    // then I do not need to specify the model's table name.
}
