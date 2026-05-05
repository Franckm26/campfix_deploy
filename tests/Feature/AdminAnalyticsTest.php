<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Concern;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->adminUser = User::create([
            'name' => 'Admin User',
            'role' => 'mis',
            'email' => 'admin@novaliches.sti.edu.ph',
            'password' => bcrypt('password'),
        ]);

        // Create a category
        $this->category = Category::create([
            'name' => 'Electrical',
        ]);
    }

    /** @test */
    public function it_displays_analytics_page_for_admin_users()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/analytics');

        $response->assertStatus(200);
        $response->assertViewIs('admin.analytics');
    }

    /** @test */
    public function it_includes_top_issue_in_location_statistics()
    {
        // Create multiple concerns for the same location with different titles
        Concern::create([
            'user_id' => $this->adminUser->id,
            'category' => $this->category->id,
            'location' => 'Room 101',
            'title' => 'Broken Light',
            'description' => 'Light is broken',
            'status' => 'Resolved',
            'cost' => 100,
        ]);

        Concern::create([
            'user_id' => $this->adminUser->id,
            'category' => $this->category->id,
            'location' => 'Room 101',
            'title' => 'Broken Light',
            'description' => 'Another broken light',
            'status' => 'Resolved',
            'cost' => 150,
        ]);

        Concern::create([
            'user_id' => $this->adminUser->id,
            'category' => $this->category->id,
            'location' => 'Room 101',
            'title' => 'Faulty Socket',
            'description' => 'Socket not working',
            'status' => 'Resolved',
            'cost' => 50,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/analytics');

        $response->assertStatus(200);

        // Verify the view has location stats with top_issue
        $locationStats = $response->viewData('locationStats');
        
        $this->assertNotNull($locationStats);
        $this->assertGreaterThan(0, $locationStats->count());

        $room101Stats = $locationStats->firstWhere('location', 'Room 101');
        
        $this->assertNotNull($room101Stats);
        $this->assertEquals('Room 101', $room101Stats['location']);
        $this->assertEquals(3, $room101Stats['count']);
        $this->assertEquals(300, $room101Stats['total_cost']);
        $this->assertEquals('Broken Light', $room101Stats['top_issue']); // Most common issue
    }

    /** @test */
    public function it_shows_various_issues_when_no_title_exists()
    {
        // Create concerns without titles
        Concern::create([
            'user_id' => $this->adminUser->id,
            'category' => $this->category->id,
            'location' => 'Room 202',
            'title' => null,
            'description' => 'Some issue',
            'status' => 'Resolved',
            'cost' => 100,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/analytics');

        $response->assertStatus(200);

        $locationStats = $response->viewData('locationStats');
        $room202Stats = $locationStats->firstWhere('location', 'Room 202');

        $this->assertNotNull($room202Stats);
        $this->assertEquals('Various Issues', $room202Stats['top_issue']);
    }

    /** @test */
    public function it_redirects_non_admin_users()
    {
        $studentUser = User::create([
            'name' => 'Student User',
            'role' => 'student',
            'email' => 'student@novaliches.sti.edu.ph',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($studentUser)
            ->get('/admin/analytics');

        $response->assertRedirect('/dashboard');
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->get('/admin/analytics');

        $response->assertRedirect('/');
    }
}
