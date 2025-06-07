<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Contains the standard resource methods: index, store, update, destroy.
 * 
 * Routes are defined as follows:
 * GET /books           -> index
 * POST /books          -> store
 * PUT /books/{id}      -> update
 * DELETE /books/{id}   -> destroy
 */
class BookController
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
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        // Get the optional parameter for search.
        $search = $request->query('search');

        // Get the optional parameter for sort, by default sorting is by title.
        $sort = $request->query('sort', 'title');

        // Get the optional parameter for the sorting direction, by default sorting is done in ascending order (alphabetical order).
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
     * @param Request $request
     * @return JsonResponse
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

        // Create a new book in the database. We use mass-assignment as defined in the Book model.
        $book = Book::create($validated);

        // Redirect to the views/books/index.blade.php page with a success message.
        return redirect()->route('books.index')->with('success', 'Book added successfully.');
    }

    /**
     * Update a Book object's attributes in the database.
     * 
     * Require a body in the request with at least one of the Book object's attributes.
     * Each attribute must be at most be 255 characters long.
     * 
     * Need the book to be updated for identification.
     * 
     * @param Request $request
     * @param Book $book
     * @return JsonResponse
     */
    public function update(Request $request, Book $book)
    {
        // Get only the title and author fields.
        $data = $request->only(['title', 'author']);

        // Because I allow the user to modify either the title or the author of the book, I need to check for null or empty strings.
        $filtered = array_filter($data, function ($value) {
            return $value !== null && $value !== '';
        });

        // Rules are necessary to validate only what has been filtered.
        $rules = [];
        if (array_key_exists('title', $filtered)) {
            $rules['title'] = 'string|max:255';
        }
        if (array_key_exists('author', $filtered)) {
            $rules['author'] = 'string|max:255';
        }

        // Validate the filtered fields to ensure they meet the 255 characters limit.
        $validated = validator($filtered, $rules)->validate();

        // Update the book's title and author.
        $book->update($validated);

        // Redirect to the views/books/index.blade.php page with a success message.
        return redirect()->route('books.index')->with('success', 'Book updated successfully.');
    }

    /**
     * Remove the specified Book object from the database.
     * 
     * @param Book $book
     * @return JsonResponse
     */
    public function destroy(Book $book)
    {
        // Delete the book using its id.
        // Note to self: remember that Book extends Model. Each instance in the database has a unique ID (incrementing) even though it was never explicitly declared.
        $book->delete();

        // Redirect to the views/books/index.blade.php page with a success message.
        return redirect()->route('books.index')->with('success', 'Book deleted successfully.');
    }

    /**
     * Export the parts of the database specified by $type in the specified $format.
     * 
     * Available export types are:
     * - 'all' for both the titles and authors of the books
     * - 'titles' for only the titles of the books
     * - 'authors' for only the authors of the books
     * 
     * Available formats are:
     * - 'csv' for a CSV file
     * - 'xml' for a XML file
     * 
     * @param string $type      'all', 'titles', 'authors
     * @param string $format    'csv', 'xml'
     * @return StreamedResponse|Response
     */
    public function export(string $type, string $format)
    {
        // Note to self: apparently it is possible to not write the type of the arguments and just have export($type, $format).
        // Note to self: this is too confusing for a PHP beginner like me coming from Java/C/... but I should keep it in mind.

        // List of the allowed types for exports. Will be used to validate $type.
        $allowedTypes = ['all', 'titles', 'authors'];

        // List of the allowed formats for exports. Will be used to validate $format. 
        $allowedFormats = ['csv', 'xml'];

        // Check if $type and $format are allowed, abort if not.
        if (!in_array($type, $allowedTypes) || !in_array($format, $allowedFormats)) {
            // Brutal but since we should design an UI that makes this impossible, we do want to crash here during development so as to fix any related issue.
            abort(404);
        }

        // Get all the books in the database.
        $books = Book::all();

        // Filling rows according to $type.
        $rows = [];
        if ($type === 'all') {
            // Note to self: in PHP this is called an associative array.
            $rows = $books->map(fn($book) => ['title' => $book->title, 'author' => $book->author]);
        } elseif ($type === 'titles') {
            $rows = $books->map(fn($book) => ['title' => $book->title]);
        } else {
            $rows = $books->map(fn($book) => ['author' => $book->author]);
        }

        // Return according to $format.
        if ($format === 'csv') {
            return $this->exportAsCSV($rows, $type);
        } else {
            return $this->exportAsXML($rows, $type);
        }
    }

    /**
     * Create the CSV file based on the provided $rows to export and $type.
     * 
     * $type ('all', 'titles', 'authors') is used for the filename.
     * 
     * @param Collection $rows
     * @param string $type
     * @return StreamedResponse
     */
    private function exportAsCSV(Collection $rows, string $type) {
        // Define the name of the CSV file to be created.
        $filename = "books_{$type}.csv";

        // Response header for CSV file downloads
        $headers = [
            'Content-Type' => 'text/csv', // Tells the browser that this is a CSV file.
            'Content-Disposition' => "attachment; filename=\"$filename\"", // Tells the browser to download (attachment) the file, sets the name of the file to $filename.
        ];

        // Note to self: 200 is HTTP status code for success.
        return new StreamedResponse(function () use ($rows) {
            // This is like C.
            $handle = fopen('php://output', 'w');

            // Only write if we have at least one book in the database.
            if (count($rows) > 0) {
                // Write the CSV headers.
                fputcsv($handle, array_keys($rows[0]));

                // Add all books to the CSV
                foreach ($rows as $row) {
                    fputcsv($handle, $row);
                }
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Create the XML file based on the provided $rows to export and $type.
     * 
     * $type ('all', 'titles', 'authors') is used for the filename.
     * 
     * @param Collection $rows
     * @param string $type
     * @return Response
     */
    private function exportAsXML(Collection $rows, string $type) {
        // Define the name of the XML file to be created.
        $filename = "books_{$type}.xml";

        // Define a new XML document
        $xml = new \SimpleXMLElement('<books/>');

        // Add all books to the XML.
        foreach ($rows as $row) {
            // Each book should be in its own <book></book> tag.
            $book = $xml->addChild('book');
            
            // Add book's attributes based on how $rows was created in the export method (depends on $type).
            foreach ($row as $key => $value) {
                $book->addChild($key, htmlspecialchars($value));
            }
        }

        // Response header for XML file downloads
        return response($xml->asXML(), 200, [
            'Content-Type' => 'application/xml', // Tells the browser that this is a CSV file.
            'Content-Disposition' => "attachment; filename=\"$filename\"", // Tells the browser to download (attachment) the file, sets the name of the file to $filename.
        ]);
    } 
}
