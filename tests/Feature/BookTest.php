<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Book;

/**
 * Use AAA
 */
class BookTest extends TestCase
{
    use RefreshDatabase;

    // Note to self: for PHPUnit to recognizes a test method as such, the name of the method must start by "test".

    /**
     * Tests that a book can be added to the database.
     *
     * Sends a POST request to /books with valid title and author,
     * and asserts that the database contains the new book.
     * 
     * Asserts successful redirect as well.
     */
    public function testAddBookToDatabase()
    {
        // Act
        $response = $this->post('/books', [
            'title' => 'fake title',
            'author' => 'fake author',
        ]);

        // Assert
        $response->assertRedirect('/books');
        $this->assertDatabaseHas('books', [
            'title' => 'fake title',
            'author' => 'fake author',
        ]);
    }

    /**
     * Tests that a book can be updated in the database.
     *
     * Creates a book, then sends a PUT request to /books/{id} with a new title and a new author. 
     * Asserts that the database reflects the updated values.
     * 
     * Asserts successful redirect as well.
     */
    public function testUpdateBookInDatabase()
    {
        // Arrange
        $book = Book::create(['title' => 'fake title', 'author' => 'fake author']);

        // Act
        $response = $this->put("/books/{$book->id}", [
            'title' => 'fake title updated',
            'author' => 'fake author updated',
        ]);

        // Assert
        $response->assertRedirect('/books');
        $this->assertDatabaseHas('books', [
            // ID check is necessary to verify that it is indeed the same book that we have correctly updated
            'id' => $book->id,
            'title' => 'fake title updated',
            'author' => 'fake author updated',
        ]);
    }

    /**
     * Tests that a book can be deleted from the database.
     *
     * Creates a book, sends a DELETE request to /books/{id},
     * and asserts that the book no longer exists in the database.
     * 
     * Asserts successful redirect as well.
     */
    public function testDeleteBookFromDatabase()
    {
        // Arrange
        $book = Book::create(['title' => 'fake title', 'author' => 'fake author']);

        // Act
        $response = $this->delete("books/{$book->id}");

        // Assert
        $response->assertRedirect('/books');
        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    /**
     * Tests that the database can be exported as a CSV file.
     *
     * Creates a book, sends a GET request to /books/export/all/csv,
     * and asserts that the response has a 200 status, that the content-type header is text/csv
     * and that the CSV contains the correct title and author.
     */
    public function testExportCsv()
    {
        // Arrange
        Book::create(['title' => 'fake title', 'author' => 'fake author']);

        // Act
        $response = $this->get('/books/export/all/csv');

        // Assert
        $response->assertStatus(200); // Note to self: Checking that the request was successful.
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type')); // Note to self: Checking that the media type was indeed CSV.
        $this->assertStringContainsString('"fake title"', $response->getContent());
        $this->assertStringContainsString('"fake author"', $response->getContent());
    }

    /**
     * Tests that the database can be exported as a XML file.
     *
     * Creates a book, sends a GET request to /books/export/all/xml,
     * and asserts that the response has a 200 status, that the content-type header is application/xml
     * and that the XML contains the correct title and author.
     */
    public function testExportXml()
    {
        // Arrange
        Book::create(['title' => 'fake title', 'author' => 'fake author']);

        // Act
        $response = $this->get('/books/export/all/xml');

        // Assert
        $response->assertStatus(200);
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('fake title', $response->getContent());
        $this->assertStringContainsString('fake author', $response->getContent());
    }

    /**
     * Tests that we do indeed fail if we try to export with either the wrong $type or the wrong $format.
     * (Notations refer to BookController.export())
     */
    public function testInvalidExport() 
    {
        // Act
        $responseWrongType = $this->get('/books/export/date/csv'); // BookController.export expects $type to be all, titles or authors.
        $responseWrongFormat = $this->get('/books/export/all/pdf'); // BookController.export expects $format to be csv or xml.

        // Assert
        $responseWrongType->assertStatus(404);
        $responseWrongFormat->assertStatus(404);
    }

    /**
     * Tests that cliking on export with an empty database does not fail.
     */
    public function testEmptyExport()
    {
        // Act
        $responseEmptyExport = $this->get('/books/export/all/csv');

        // Assert
        $responseEmptyExport->assertStatus(200);
        $this->assertStringContainsString('title', $responseEmptyExport->getContent()); // Should at least have a CSV header.
    }

    /**
     * Tests that the database can be searched by book title and author.
     */
    public function testBookSearchFunctionality()
    {
        // Arrange
        Book::create(['title' => 'fake title', 'author' => 'fake author']);
        Book::create(['title' => 'eltit ekaf', 'author' => 'rohtua ekaf']); // Mirror of the above to ensure that we can only get one book as a search result.

        // Act
        $responseTitleBook1 = $this->get('/books?search=fake title');
        $responseTitleBook2 = $this->get('/books?search=eltit ekaf');
        $responseAuthorBook1 = $this->get('/books?search=fake author');
        $responseAuthorBook2 = $this->get('/books?search=rohtua ekaf');

        // Assert
        $responseTitleBook1->assertSee('fake title');
        $responseTitleBook1->assertDontSee('eltit ekaf');
        $responseTitleBook2->assertSee('eltit ekaf');
        $responseTitleBook2->assertDontSee('fake title');
        $responseAuthorBook1->assertSee('fake author');
        $responseAuthorBook1->assertDontSee('rohtua ekaf');
        $responseAuthorBook2->assertSee('rohtua ekaf');
        $responseAuthorBook2->assertDontSee('fake author');
    }

    /**
     * Tests that books can be sorted by title and author in alphabetical order and reversed alphabetical order.
     */
    public function testBookSortFunctionality()
    {
        // Arrange
        Book::create(['title' => 'title AAA', 'author' => 'author AAA']);
        Book::create(['title' => 'title ZZZ', 'author' => 'author ZZZ']);

        // Act - title descending
        $responseTitleDesc = $this->get('/books?sort=title&direction=desc');
        // Assert - title descending
        $responseTitleDesc->assertSeeInOrder(['title ZZZ', 'title AAA']);

        // Act - title ascending
        $responseTitleAsc = $this->get('/books?sort=title&direction=asc');
        // Assert - title ascending
        $responseTitleAsc->assertSeeInOrder(['title AAA', 'title ZZZ']);

        // Act - author descending
        $responseAuthorDesc = $this->get('/books?sort=author&direction=desc');
        // Assert - title descending
        $responseAuthorDesc->assertSeeInOrder(['author ZZZ', 'author AAA']);

        // Act - author ascending
        $responseAuthorAsc = $this->get('/books?sort=author&direction=asc');
        // Assert - author ascending
        $responseAuthorAsc->assertSeeInOrder(['author AAA', 'author ZZZ']);
    }
}
