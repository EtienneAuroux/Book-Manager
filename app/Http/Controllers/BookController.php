<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

/**
 * Contains the standard resource methods: index, store, update, destroy.
 * 
 * Routes are defined as follow:
 * GET /books           -> index
 * POST /books          -> store
 * PUT /books/{id}      -> update
 * DELETE /books/{id}   -> destroy
 */
class BookController extends Controller
{
    /**
     * Display a listing of the books.
     * 
     * Support search and sort query parameters by title or author.
     * 
     * Query parameters:
     * - (optional) string search
     * - (optional) string sort -> title or author (default is title)
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Get the optional parameter for search.
        $search = $request->query('search');

        // Get the optional parameter for sort, by default sorting is by title.
        $sort = $request->query('sort', 'title');

        // Get the optional parameter for the sorting direction, by default sorting is done ascendantly (alphabetical order).
        $direction = $request->query('direction', 'asc');

        // Get the static method query() from the class Book.
        // Note to self, :: is to access static members while -> is for instance members.
        $query = Book::query();

        // If query is a search, filters by title or by author.
        if ($search) {
            $query->where('title', 'like', "%{$search}%")->orWhere('author', 'like', "%{$search}%");
        }
        
        // If query is a sort, sorts by title or by author.
        // First identify $sort in the list of possible sorting parameters, then orderBy according to the direction.
        if (in_array($sort, ['title', 'author']) && in_array($direction, ['asc', 'desc'])) {
            $query->orderBy($sort, $direction);
        }

        // Get the list of books resulting from the request.
        $books = $query->get();
        
        // Return the views/books/index.blade.php page.
        return view('books.index', compact('books', 'sort', 'direction'));
    }

    /**
     * Store a new Book object in the database.
     * 
     * Require a body in the request with a Book object's attributes.
     * The attributes are string of at most 255 characters.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Check if the incoming Book's attributes are valid.
        // Since we deal with a book's title and author it makes sense to check for string length.
        // Side note: In the case of numerous co-authors this might block.
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
        ]);

        // Create a new book in the database. We use mass-alignment as defined in the Book model.
        $book = Book::create($validated);

        // Redirect to the views/books/index.blade.php page with a success message.
        return redirect()->route('books.index')->with('success', 'Book added successfully.');
    }

    /**
     * Update a Book object's attributes in the database.
     * 
     * Require a body in the request with at least on of the Book object's attributes.
     * The attribute(s) can at most be 255 characters long.
     * 
     * Need the book to be updated for identification.
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Model\Book $book
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Book $book)
    {
        // Check if the incoming Book's attributes are valid.
        // Since we deal with a book's title and author it makes sense to check for string length.
        // Side note: In the case of numerous co-authors this might block.
        // "sometimes" allows partial updates.
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'author' => 'sometimes|required|string|max:255',
        ]);

        // Update the book's title and author.
        $book->update($validated);

        // Return the updated book.
        return response()->json($book);
    }

    /**
     * Remove the specified Book object from the database.
     * 
     * @param \App\Model\Book $book
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Book $book)
    {
        // Delete the book using its id.
        // Note to self: remember that Book extends Model. Each instance in the database has a unique ID (incrementing) even though it was never explicitly declared.
        $book->delete();

        // Redirect to the views/books/index.blade.php page with a success message.
        return redirect()->route('books.index')->with('success', 'Book deleted successfully.');
    }
}
