<?php

namespace Tests\Feature;

use App\Models\AdoptionApplication;
use App\Models\Pet;
use App\Models\TemperamentTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_applied_pet_remains_in_recommendations_with_applied_status(): void
    {
        $adopter = User::factory()->create([
            'role' => 'adopter',
            'email' => 'adopter@example.com',
            'email_verified_at' => now(),
        ]);

        $tag = TemperamentTag::firstOrCreate(['name' => 'Playful']);

        $pet1 = Pet::create([
            'name'   => 'King',
            'breed'  => 'Aspin',
            'color'  => 'Brown',
            'gender' => 'male',
            'type'   => 'dog',
            'status' => 'available',
        ]);
        $pet1->temperamentTags()->attach($tag);

        $pet2 = Pet::create([
            'name'   => 'Barnaby',
            'breed'  => 'Aspin',
            'color'  => 'Golden',
            'gender' => 'male',
            'type'   => 'dog',
            'status' => 'available',
        ]);
        $pet2->temperamentTags()->attach($tag);

        // First recommendations request
        $response1 = $this->actingAs($adopter, 'sanctum')->postJson('/api/recommendations/match', [
            'preferred_species'    => 'dog',
            'living_environment'   => 'house_fenced',
            'activity_level'       => 'high',
            'desired_temperaments' => ['Playful'],
        ]);

        $response1->assertOk();
        $recs1 = $response1->json('recommendations');
        $this->assertNotEmpty($recs1);

        $pet1RecBefore = collect($recs1)->firstWhere('id', $pet1->id);
        $this->assertNotNull($pet1RecBefore, 'Pet 1 (King) should be recommended initially');
        $this->assertFalse($pet1RecBefore['is_applied']);
        $this->assertNull($pet1RecBefore['application_status']);
        $this->assertTrue($pet1RecBefore['can_apply']);

        // Adopter submits application for Pet 1 (King)
        AdoptionApplication::create([
            'pet_id'              => $pet1->id,
            'applicant_name'      => $adopter->name,
            'applicant_email'     => $adopter->email,
            'applicant_phone'     => '09123456789',
            'id_type'             => 'Philippine National ID (PhilSys)',
            'home_ownership'      => 'owned',
            'employment_status'   => 'employed',
            'message'             => 'Applying for King',
            'status'              => 'pending',
            'application_source'  => 'recommendation',
            'compatibility_score' => 95,
        ]);

        // Second recommendations request
        $response2 = $this->actingAs($adopter, 'sanctum')->postJson('/api/recommendations/match', [
            'preferred_species'    => 'dog',
            'living_environment'   => 'house_fenced',
            'activity_level'       => 'high',
            'desired_temperaments' => ['Playful'],
        ]);

        $response2->assertOk();
        $recs2 = $response2->json('recommendations');
        $this->assertNotEmpty($recs2);

        $pet1RecAfter = collect($recs2)->firstWhere('id', $pet1->id);
        $this->assertNotNull($pet1RecAfter, 'Pet 1 (King) must NOT be removed or replaced after applying');
        $this->assertTrue($pet1RecAfter['is_applied']);
        $this->assertSame('pending', $pet1RecAfter['application_status']);
        $this->assertFalse($pet1RecAfter['can_apply']);

        // Verify other unapplied pets still allow application
        $pet2Rec = collect($recs2)->firstWhere('id', $pet2->id);
        if ($pet2Rec) {
            $this->assertFalse($pet2Rec['is_applied']);
            $this->assertNull($pet2Rec['application_status']);
            $this->assertTrue($pet2Rec['can_apply']);
        }
    }
}
