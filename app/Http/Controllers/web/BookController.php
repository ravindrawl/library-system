<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Services\BookService;
use Exception;
use GuzzleHttp\Psr7\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Author: Ravindra soleravi@gmail.com
 * Date : 2024/11/07
 * BookController
 * handle book CRUD - WEB 
 */

class BookController extends Controller
{

    protected $bookService;

    public function __construct(BookService $bookService)
    {
        $this->bookService = $bookService;
    }
    /**
     * index
     * return all books  including soft-deleted
     */
    public function index()
    {
        $books = $this->bookService->getAllBooks();

        return view('books.index', compact('books'));
    }
    /**
     * create a book route
     * 
     */
    public function store(Request $request)
    {
        Log::channel('info')->critical("createBoook started");
        $rules = [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|unique:books,isbn,',
            'year_published' => 'required|integer|min:1000|max:' . date('Y'),
            'category_id' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        DB::beginTransaction(); // Start the transaction
        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('images', 'public');
            }

            $bookData = [
                'title' => $request['title'],
                'author' => $request['author'],
                'year_published' => $request['year_published'],
                'category_id' => $request['category_id'],
                'isbn' => $request['isbn'],
                'created_by' => Auth::id(),
                'description' => $request['description'],
                'image_path' => $imagePath
            ];

            $this->bookService->createBook($bookData);
            DB::commit();
            return response()->json(['message', 'Book created successfully'], 201);
        } catch (Exception $e) {
            DB::rollBack();
            if (isset($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            Log::channel('errorlog')->critical("Error in BookController@store: " . $e->getMessage(), [
                'exception' => $e,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json(['error' => $e . 'Data Saving Fail.!'], 422);
        }
    }

    /**
     * Show a book 
     * by Id
     */
    public function show($id)
    {
        $rules = [
            'id' => 'required|integer'
        ];

        $data = ['id' => $id];
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            $book = $this->bookService->findBookById($id);
            $book->image_path = asset('storage/' . $book->image_path);

            if (!$book) {
                return response()->json(['error' => 'Book Details Not Found']);
            }
            return response()->json($book);
        } catch (Exception $e) {
            Log::channel('errorlog')->critical("Error in BookController@show: " . $e->getMessage(), [
                'exception' => $e,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json(['error' => 'Book update failed!']);
        }
    }

    /**
     * update a book 
     * 
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|unique:books,isbn,',
            'year_published' => 'required|integer|min:1000|max:' . date('Y'),
            'category_id' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        DB::beginTransaction(); // Start the transaction
        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('images', 'public');
            }

            $bookData = [
                'title' => $request['title'],
                'author' => $request['author'],
                'year_published' => $request['year_published'],
                'category_id' => $request['category_id'],
                'isbn' => $request['isbn'],
                'created_by' => Auth::id(),
                'description' => $request['description'],
                'image_path' => $imagePath
            ];

            $book = $this->bookService->updateBook($id, $bookData);
            DB::commit();
            return response()->json(['message' => 'Book created successfully!', 'book' => $book], 201);
        } catch (Exception $e) {
            DB::rollBack();
            if (isset($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            Log::channel('errorlog')->critical("Error in BookController@update: " . $e->getMessage(), [
                'exception' => $e,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json(['error' => 'Book update failed!']);
        }
    }
    /**
     * soft delete a book 
     * 
     */
    public function destroy($id)
    {
        $rules = [
            'id' => 'required|integer'
        ];

        $data = ['id' => $id];
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        /*
               before perform deletion, should check borrowing status                 
            **/
        try {
            // only created user can delete the book
            $book = $this->bookService->findBookById($id);
            if ($book->created_by != Auth::id()) {
                return response()->json(['denide' => 'Do not have permission to delete this Book!']);
            }
            $book = $this->bookService->deleteBook($id);
            return response()->json(['message' => 'Book deleted successfully', 'bookId' => $book->id], 201);
        } catch (Exception $e) {
            Log::channel('errorlog')->critical("Error in BookController@destroy: " . $e->getMessage(), [
                'exception' => $e,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json(['error' => 'Book Delete failed!']);
        }
    }
}
