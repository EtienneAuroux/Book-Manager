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
}
