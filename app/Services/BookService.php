<?php
// app/Services/BookService.php

namespace App\Services;

use App\Models\Book;
use App\Models\BookCategory;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;


/**
 * Author: Ravindra soleravi@gmail.com
 * Date : 2024/11/07
 * BookService class
 * handle book CRUD logic and deal with database
 * common for API and Web 
 */
class BookService
{
    /**
     * getAllBooks including soft-deleted
     * 
     */
    public function getAllBooks($paginate = 5)
    {
        try {
            return Book::withTrashed()
                ->with('category')
                ->orderBy('created_at', 'desc')
                ->paginate($paginate);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * findBookById a book by book id
     * 
     */
    public function findBookById($id)
    {
        try {
            return Book::withTrashed()
                ->with('category')->findOrFail($id);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * create a book by book
     * 
     */
    public function createBook(array $data)
    {
        try {
            return  Book::create($data);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * updateBook a book by book-id
     * 
     */
    public function updateBook($id, array $data)
    {
        try {
            $book = $this->findBookById($id);
            $updateData = [
                'title' => $data['title'] ?? $book->title,
                'author' => $data['author'] ?? $book->author,
                'year_published' => $data['year_published'] ?? $book->year_published,
                'category_id' => $data['category_id'] ??   $book->category_id,
                'isbn' => $data['isbn']
            ];

            $book->update($updateData);
            return $book;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * deleteBook a book - soft-delete
     * 
     */
    public function deleteBook($id)
    {
        try {
            $book = $this->findBookById($id);
            $book->delete();
            return $book;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /*TODO
       need service to reactive deleted book 
    **/

    /*
       validate data when store a book - via REST API
    **/
    public function validateData(array $data)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'year_published' => 'required|integer|min:1000|max:' . date('Y'),
            'isbn' => 'required|string|unique:books',
            'category_id' => 'required|exists:categories,id'
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return $validator;
    }

    /*
       validate data when put a book - via REST API
    **/
    public function validateUpdateData(array $data, $id)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'year_published' => 'required|integer|min:1000|max:' . date('Y'),
            'category_id' => 'required|exists:categories,id', // Assumes category_id exists in a categories table
            'isbn' => 'nullable|string|max:13',
            'isbn' => 'sometimes|string|unique:books,isbn,' . $id,
            'cover' => 'nullable|file|mimes:jpg,png,jpeg|max:2048', // Optional file validation for cover
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return $validator;
    }

    /*
       get all book categories
    **/
    public function getAllcategories()
    {
        try {
            return BookCategory::all();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
