<?php

namespace App\Http\Controllers\web;

use App\Services\BookService;
use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $bookService;

    public function __construct(BookService $bookService)
    {
        $this->bookService = $bookService;
    }
    public function index()
    {
        $bookList  = $this->bookService->getAllBooks();
        $catagoryList = $this->bookService->getAllcategories();
        //dd($bookList);
        return view('dashboard', [
            'booksData' => [
                'books' => $bookList,
                'categories' => $catagoryList,
            ]
        ]);
    }
}
