<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Models\User;


/**
 * Author: Ravindra : soleravi@gmail.com
 * Date : 2024/11/11
 * BookController test cases 
 */
class BookControllerTest extends TestCase
{
    use RefreshDatabase;
    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('public'); // Mock the public disk for file storage
        Log::shouldReceive('channel')->andReturnSelf();
        Log::shouldReceive('critical')->andReturnNull(); // Mock logging
    }

    /** @test */
    public function it_can_store_a_book_successfully()
    {
        // Act as an authenticated user
        $user = User::factory()->create();
        Auth::login($user);

        // Prepare mock request data
        $bookData = [
            'title' => 'Sample Book',
            'author' => 'Sample Author',
            'isbn' => '1234567890123',
            'year_published' => 2023,
            'category_id' => 1,
            'description' => 'Sample Description',
            'image' => UploadedFile::fake()->image('cover.jpg'),
        ];

        // Act - Send a POST request to store a book
        $response = $this->postJson(route('books.store'), $bookData);

        // Assert - Check if the response is successful
        $response->assertStatus(201)
            ->assertJson(['message' => 'Book created successfully']);

        // Assert - Check if the book was saved in the database
        $this->assertDatabaseHas('books', [
            'title' => 'Sample Book',
            'author' => 'Sample Author',
            'isbn' => '1234567890123',
            'created_by' => $user->id,
        ]);

        // Assert - Check if the image file was stored
        Storage::disk('public')->assertExists('images/' . $bookData['image']->hashName());
    }
}
