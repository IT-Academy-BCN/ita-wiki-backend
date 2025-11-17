<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Enums\LanguageEnum;
use App\Models\User;

class TechnicalTestUploadPdfTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_upload_a_pdf_file()
    {
        Storage::fake('local');

     
        // $user = $this->authenticateUserWithRole('mentor');
        $githubId = 123456;
        User::factory()->create(['github_id' => $githubId]);

        $payload = [
            'title' => 'Prueba técnica con PDF',
            'language' => LanguageEnum::PHP->value,
            'description' => 'Descripción de prueba',
            'tags' => ['php', 'laravel'],
            'github_id' => $githubId,
        ];

        $file = UploadedFile::fake()->create('prueba.pdf', 100, 'application/pdf');

        $response = $this->postJson(route('technical-tests.store'), array_merge($payload, [
            'file' => $file,
        ]));

        $response->assertStatus(201);
        $data = $response->json('data');
        $this->assertNotNull($data['file_path'] ?? null, 'El campo file_path debe estar presente en la respuesta');
        $this->assertDatabaseHas('technical_tests', [
            'title' => 'Prueba técnica con PDF',
            'file_original_name' => 'prueba.pdf',
            'file_path' => $data['file_path'],
        ]);
        $this->assertTrue(Storage::disk('local')->exists($data['file_path']));
    }
}
