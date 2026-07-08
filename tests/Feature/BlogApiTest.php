<?php

namespace Tests\Feature;

use App\Models\Blog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_returns_published_blogs(): void
    {
        Blog::create([
            'title' => 'Join The FlySafair Team',
            'heading' => 'Great vision without great people is irrelevant',
            'description' => 'At FlySafair, we make it our mission to unite people with who and what they love.',
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/blogs');

        $response->assertOk()
            ->assertJsonFragment([
                'title' => 'Join The FlySafair Team',
            ]);
    }

    public function test_api_blog_posts_route_returns_published_blogs(): void
    {
        Blog::create([
            'title' => 'Public Blog Post',
            'heading' => 'A public blog for the frontend',
            'description' => 'This blog should be visible in the public blog feed.',
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/blog/posts');

        $response->assertOk()
            ->assertJsonFragment([
                'title' => 'Public Blog Post',
            ]);
    }

    public function test_api_blog_post_detail_route_returns_published_blog(): void
    {
        $blog = Blog::create([
            'title' => 'Blog Detail Post',
            'heading' => 'Detail route test',
            'description' => 'Detailed blog content should be returned.',
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/blog/posts/' . $blog->id);

        $response->assertOk()
            ->assertJsonFragment([
                'title' => 'Blog Detail Post',
                'description' => 'Detailed blog content should be returned.',
            ]);
    }
}
