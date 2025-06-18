<!DOCTYPE html>
<html>
    <head>
        <title>Book Manager</title>
        <meta charset="UTF-8">
        <!-- Using bootstrap for styling. (Familiar to me since it is used with DASH in Python) -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    </head>
    <body class="p-4">
        <div class="container">
            <h1 class="mb-4">Yaraku's book manager</h1>
            
            <!-- Notification of successful action (add, delete, update) -->
            @if(session('success'))
                <div id="success-alert" class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <!-- Form to search a book in the database -->
            <form action="{{ route('books.index') }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-8">
                    <input type="text" name="search" class="form-control" placeholder="Search by title or author" value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <input type="hidden" name="sort" value="{{ request('sort', 'title') }}">
                    <input type="hidden" name="direction" value="{{ request('direction', 'asc') }}">
                    <button type="submit" class="btn btn-secondary w-100">Search</button>
                </div>
            </form>
            <!-- Form to add a book to the database -->
            <form action="{{ route('books.store') }}" method="POST" class="row g-3 mb-4">
                <!-- CSRF token to protect against cross-site request forgery -->
                @csrf
                <div class="col-md-4">
                    <input type="text" name="title" class="form-control" placeholder="Book Title" required>
                </div>
                <div class="col-md-4">
                    <input type="text" name="author" class="form-control" placeholder="Author Name" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Add Book</button>
                </div>
            </form>

            <!-- Table of books with sortable column headers (Title, Author) -->
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>
                            <!-- Sort books by titles when clicked in alphabetical or reverse alphabetical order -->
                            <a href="{{ route('books.index', ['sort' => 'title', 'direction' => ($sort === 'title' && $direction === 'asc') ? 'desc' : 'asc', 'search' => request('search')]) }}">
                                Title
                                @if ($sort === 'title')
                                    {{ $direction === 'asc' ? '↑' : '↓' }}
                                @endif
                            </a>
                        </th>
                        <th>
                            <!-- Sort books by authors when clicked in alphabetical or reverse alphabetical order -->
                            <a href="{{ route('books.index', ['sort' => 'author', 'direction' => ($sort === 'author' && $direction === 'asc') ? 'desc' : 'asc', 'search' => request('search')]) }}">
                                Author
                                @if ($sort === 'author')
                                    {{ $direction === 'asc' ? '↑' : '↓' }}
                                @endif
                            </a>
                        </th>
                        <th>Update</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loop through books and create one row per book with Title|Author|Edit/Update(button)|Delete(button) -->
                    @foreach ($books as $book)
                        <tr id="book-row-{{ $book->id }}">
                            <!-- Form to update the book's title and/or author of the corresponding row -->
                            <form action="{{ route('books.update', $book->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <!-- Title of the book -->
                                <td>
                                    <span class="static-title">{{ $book->title }}</span>
                                    <input type="text" name="title" value="{{ $book->title }}" class="form-control d-none editable-title">
                                </td>

                                <!-- Author of the book -->
                                <td>
                                    <span class="static-author">{{ $book->author }}</span>
                                    <input type="text" name="author" value="{{ $book->author }}" class="form-control d-none editable-author">
                                </td>

                                <!-- Edit/Update button -->
                                <td class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-primary edit-btn">Edit</button>
                                    <!-- Because the update button has type "submit", it will trigger the PUT. -->
                                    <button type="submit" class="btn btn-sm btn-success d-none update-btn">Update</button>
                                </td>
                            </form>
                            <!-- Delete button -->
                            <td>
                                <!-- Form to delete the current book, with confirmation prompt -->
                                <form action="{{ route('books.destroy', $book->id) }}" method="POST" onsubmit="return confirm('Delete this book?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    <!-- If there is no book in the database, shows a message. -->
                    @if ($books->isEmpty())
                        <tr>
                            <td colspan="4">You have no books registered.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Section to export the database in CSV or XML format -->
        <div class="container">
            <h4 class="mt-4">Export Books in CSV or XML files</h4>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-2 mt-2">
                <div class="col">
                    <a href="{{ route('books.export', ['type' => 'all', 'format' => 'csv']) }}" class="btn btn-outline-success w-100">
                        CSV - Title & Author
                    </a>
                </div>
                <div class="col">
                    <a href="{{ route('books.export', ['type' => 'titles', 'format' => 'csv']) }}" class="btn btn-outline-success w-100">
                        CSV - Titles Only
                    </a>
                </div>
                <div class="col">
                    <a href="{{ route('books.export', ['type' => 'authors', 'format' => 'csv']) }}" class="btn btn-outline-success w-100">
                        CSV - Authors Only
                    </a>
                </div>
                <div class="col">
                    <a href="{{ route('books.export', ['type' => 'all', 'format' => 'xml']) }}" class="btn btn-outline-primary w-100">
                        XML - Title & Author
                    </a>
                </div>
                <div class="col">
                    <a href="{{ route('books.export', ['type' => 'titles', 'format' => 'xml']) }}" class="btn btn-outline-primary w-100">
                        XML - Titles Only
                    </a>
                </div>
                <div class="col">
                    <a href="{{ route('books.export', ['type' => 'authors', 'format' => 'xml']) }}" class="btn btn-outline-primary w-100">
                        XML - Authors Only
                    </a>
                </div>
            </div>
        </div>

        <!-- JavaScript section -->
        <script>
            // The successful action notification fades and disappears after 2 seconds.
            setTimeout(() => {
                const alert = document.getElementById('success-alert');
                if (alert) {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            }, 2000);

            // Handles the Edit button. 
            // When the user click Edit, it enables editing of the title and author of the corresponding row.
            // And the Edit button turns into an Update button.
            // (Searching by class "edit-btn" in the document, applying the logic to all type "buttons" with that class)
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const row = this.closest('tr');

                    // Disable the edit buttons of all the other rows.
                    document.querySelectorAll('.edit-btn').forEach(btn => {
                        if (btn !== this) btn.disabled = true;
                    });

                    // Enables editing for this row.
                    row.querySelector('.static-title').classList.add('d-none');
                    row.querySelector('.static-author').classList.add('d-none');
                    row.querySelector('.editable-title').classList.remove('d-none');
                    row.querySelector('.editable-author').classList.remove('d-none');

                    // Hides edit button and shows update button.
                    this.classList.add('d-none');
                    row.querySelector('.update-btn').classList.remove('d-none');
                });
            });
        </script>
    </body>
</html>
