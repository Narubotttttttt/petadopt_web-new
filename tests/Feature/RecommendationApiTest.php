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

    public function test_recommendation_engine_limits_to_one_top_pet_when_one_slot_remains(): void
    {
        $adopter = User::factory()->create([
            'role' => 'adopter',
            'email' => 'cleared.adopter@example.com',
            'email_verified_at' => now(),
        ]);

        $tag = TemperamentTag::firstOrCreate(['name' => 'Playful']);

        // Adopter previously adopted 3 pets
        $adoptedPet1 = Pet::create(['name' => 'Adopted1', 'breed' => 'Aspin', 'type' => 'dog', 'status' => 'adopted']);
        $adoptedPet2 = Pet::create(['name' => 'Adopted2', 'breed' => 'Aspin', 'type' => 'dog', 'status' => 'adopted']);
        $deceasedPet = Pet::create(['name' => 'DeceasedPet', 'breed' => 'Aspin', 'type' => 'dog', 'status' => 'deceased']);

        AdoptionApplication::create([
            'pet_id' => $adoptedPet1->id,
            'applicant_name' => $adopter->name,
            'applicant_email' => $adopter->email,
            'applicant_phone' => '09111111111',
            'status' => 'adopted',
        ]);

        AdoptionApplication::create([
            'pet_id' => $adoptedPet2->id,
            'applicant_name' => $adopter->name,
            'applicant_email' => $adopter->email,
            'applicant_phone' => '09111111111',
            'status' => 'adopted',
        ]);

        $deceasedApp = AdoptionApplication::create([
            'pet_id' => $deceasedPet->id,
            'applicant_name' => $adopter->name,
            'applicant_email' => $adopter->email,
            'applicant_phone' => '09111111111',
            'status' => 'adopted',
        ]);

        // 1 pet passed away from natural causes and was verified/cleared by shelter staff
        \App\Models\PetHealthUpdate::create([
            'pet_id'          => $deceasedPet->id,
            'user_id'         => $adopter->id,
            'photo_path'      => 'pet-updates/deceased.jpg',
            'health_status'   => 'deceased',
            'notes'           => 'Passed away naturally due to old age.',
            'check_in_date'   => now()->toDateString(),
            'review_decision' => 'cleared_natural',
            'status'          => 'reviewed',
        ]);

        // Available candidate pets in shelter
        $cand1 = Pet::create(['name' => 'TopCandidate', 'breed' => 'Aspin', 'type' => 'dog', 'status' => 'available']);
        $cand1->temperamentTags()->attach($tag);

        $cand2 = Pet::create(['name' => 'SecondCandidate', 'breed' => 'Aspin', 'type' => 'dog', 'status' => 'available']);
        $cand2->temperamentTags()->attach($tag);

        $cand3 = Pet::create(['name' => 'ThirdCandidate', 'breed' => 'Aspin', 'type' => 'dog', 'status' => 'available']);
        $cand3->temperamentTags()->attach($tag);

        // Request recommendations
        $response = $this->actingAs($adopter, 'sanctum')->postJson('/api/recommendations/match', [
            'preferred_species'    => 'dog',
            'living_environment'   => 'house_fenced',
            'activity_level'       => 'high',
            'desired_temperaments' => ['Playful'],
        ]);

        $response->assertOk();
        $this->assertSame(2, $response->json('adopted_count'));
        $this->assertSame(1, $response->json('remaining_slots'));
        $this->assertSame(1, $response->json('count'), 'When exactly 1 slot is open, engine must return only 1 pet');

        $recs = $response->json('recommendations');
        $this->assertCount(1, $recs);
        $this->assertTrue($response->json('can_retake'));
        $this->assertStringContainsString('1 available adoption slot', $response->json('comparative_analysis.summary'));
    }

    public function test_recommendation_engine_stops_recommending_when_adopter_has_three_adopted_pets(): void
    {
        $adopter = User::factory()->create([
            'role' => 'adopter',
            'email' => 'maxed.adopter@example.com',
            'email_verified_at' => now(),
        ]);

        $tag = TemperamentTag::firstOrCreate(['name' => 'Playful']);

        // Adopter currently has 3 active adopted pets
        for ($i = 1; $i <= 3; $i++) {
            $pet = Pet::create([
                'name'   => "ActivePet{$i}",
                'breed'  => 'Aspin',
                'type'   => 'dog',
                'status' => 'adopted',
            ]);

            AdoptionApplication::create([
                'pet_id'          => $pet->id,
                'applicant_name'  => $adopter->name,
                'applicant_email' => $adopter->email,
                'applicant_phone' => '09111111111',
                'status'          => 'adopted',
            ]);
        }

        // Available candidate pets in shelter
        $cand = Pet::create(['name' => 'AvailablePet', 'breed' => 'Aspin', 'type' => 'dog', 'status' => 'available']);
        $cand->temperamentTags()->attach($tag);

        // Request recommendations
        $response = $this->actingAs($adopter, 'sanctum')->postJson('/api/recommendations/match', [
            'preferred_species'    => 'dog',
            'living_environment'   => 'house_fenced',
            'activity_level'       => 'high',
            'desired_temperaments' => ['Playful'],
        ]);

        $response->assertOk();
        $this->assertTrue($response->json('max_limit_reached'));
        $this->assertSame(3, $response->json('adopted_count'));
        $this->assertSame(0, $response->json('remaining_slots'));
        $this->assertSame(0, $response->json('count'));
        $this->assertEmpty($response->json('recommendations'));
        $this->assertStringContainsString('maximum shelter adoption limit of 3 pets', $response->json('message'));
    }
}
