<!DOCTYPE html>
<html>
    <head>
        <title>Yaraku's book manager</title>
        <meta charset="UTF-8">
        <!-- Using bootstrap for easy CSS, same as DASH in Python -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    </head>
    <body class="p-4">
        <div class="container">
            <h1 class="mb-4">Yaraku's book manager</h1>
            
            <!-- Notification of successful action -->
            @if(session('success'))
                <div id="success-alert" class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <!-- Form to search a book in the database -->
            <form action="{{ route('books.index') }}" method="GET" class="row g-3 mb-4">
                @csrf
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Search by title or author" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <input type="hidden" name="sort" value="{{ request('sort', 'title') }}">
                    <input type="hidden" name="direction" value="{{ request('direction', 'asc') }}">
                    <button type="submit" class="btn btn-secondary w-100">Search</button>
                </div>
            </form>
            <!-- Form to add a book to the database -->
            <form action="{{ route('books.store') }}" method="POST" class="row g-3 mb-4">
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

            <!-- List of books -->
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>
                            <!-- Allows books to be sorted by titles in alphabetical or reverse alphabetical order -->
                            <a href="{{ route('books.index', ['sort' => 'title', 'direction' => ($sort === 'title' && $direction === 'asc') ? 'desc' : 'asc', 'search' => request('search')]) }}">
                                Title
                                @if ($sort === 'title')
                                    {{ $direction === 'asc' ? '↑' : '↓' }}
                                @endif
                            </a>
                        </th>
                        <th>
                            <!-- Allows books to be sorted by authors in alphabetical or reverse alphabetical order -->
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
                    @foreach ($books as $book)
                        <tr id="book-row-{{ $book->id }}">
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
                                    <!-- Because the update button as type "submit", it will trigger the PUT. -->
                                    <button type="submit" class="btn btn-sm btn-success d-none update-btn">Update</button>
                                </td>
                            </form>
                            <!-- Delete button -->
                            <td>
                                <form action="{{ route('books.destroy', $book->id) }}" method="POST" onsubmit="return confirm('Delete this book?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    @if ($books->isEmpty())
                        <tr>
                            <td colspan="3">You have no books registered.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <script>
            // The successful action notification fades and disappears after 3 seconds.
            setTimeout(() => {
                const alert = document.getElementById('success-alert');
                if (alert) {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            }, 2000);

            // Handles the Edit button. When the user click Edit, the title and author of the corresponding row become editable.
            // And the Edit button turns into an Update button.
            // (Searching by class "edit-btn" in the document, applying the logic to all type "buttons" with that class)
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const row = this.closest('tr');

                    // Toggle visibility
                    row.querySelector('.static-title').classList.add('d-none');
                    row.querySelector('.static-author').classList.add('d-none');
                    row.querySelector('.editable-title').classList.remove('d-none');
                    row.querySelector('.editable-author').classList.remove('d-none');

                    // Hide edit button, show update button
                    this.classList.add('d-none');
                    row.querySelector('.update-btn').classList.remove('d-none');
                });
            });
        </script>
    </body>
</html>