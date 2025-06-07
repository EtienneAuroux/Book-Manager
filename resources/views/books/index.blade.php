<!DOCTYPE html>
<html>
    <head>
        <title>Yaraku's book manager</title>
        <meta charset="UTF-8">
        <!-- Using bootstrap for easy CSS, justified by time constraint of assignment -->
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
                        <th>Title</th>
                        <th>Author</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($books as $book)
                        <tr>
                            <td>{{ $book->title }}</td>
                            <td>{{ $book->author }}</td>
                            <td>
                                <!-- TODO: Update action -->
                                <form action="{{ route('books.destroy', $book->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this book?')">Delete</button>
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
            }, 3000);
        </script>
    </body>
</html>