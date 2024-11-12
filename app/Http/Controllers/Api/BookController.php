<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\BookService;
use App\Models\Book;


use Exception;

/**
 * Author: Ravindra soleravi@gmail.com
 * Date : 2024/11/08
 * BookController
 * handle book CRUD - REST API
 */
class BookController extends Controller
{
    protected $bookService;

    public function __construct(BookService $bookService)
    {
        $this->bookService = $bookService;
    }
    /**
     * GET /api/books - Get all books (including soft-deleted)
     */
    public function index()
    {
        $books = $this->bookService->getAllBooks();
        return response()->json($books);
    }

    /**
     * POST /api/books - Create a new book
     */
    public function store(Request $request)
    {
        $validator = $this->bookService->validateData($request->all());
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $book = $this->bookService->createBook($request->all());
            return response()->json(['status' => 'success', 'message' => 'Book created successfully', 'data' => $book], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An error occurred while creating the book', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/books/{id} - Get a single book by ID (including soft-deleted)
     */
    public function show(string $id)
    {
        $rules = [
            'id' => 'required|integer'
        ];

        $data = ['id' => $id];
        $validator = Validator::make($data, $rules);
        Log::channel('errorlog')->critical($validator->fails());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $book = $this->bookService->findBookById($id);
            if (!$book) {
                return response()->json(['error' => 'Book not found'], 404);
            }

            return response()->json($book);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An error occurred while searching the book', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     *  PUT /api/books/{id} - Update a book
     */
    public function update(Request $request, string $id)
    {
        $book = Book::find($id);
        if (!$book) {
            return response()->json(['error' => 'Book not found'], 404);
        }
        $validator = $this->bookService->validateUpdateData($request->all(), $id);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $book->update($request->all());
            return response()->json(['message' => 'Book updated successfully', 'book' => $book]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An error occurred while updating the book', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     *   DELETE /api/books/{id} - Soft delete a book
     */
    public function destroy(string $id)
    {
        $rules = [
            'id' => 'required|integer'
        ];

        $data = ['id' => $id];
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $book = Book::find($id);
        if (!$book) {
            return response()->json(['error' => 'Book not found'], 404);
        }

        try {
            $book = $this->bookService->deleteBook($id);
            return response()->json(['message' => 'Book soft deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An error occurred while Deleting the book', 'details' => $e->getMessage()], 500);
        }
    }
}
