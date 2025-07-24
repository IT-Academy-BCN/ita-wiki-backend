<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Enums\LanguageEnum;

class TechnicalTestUploadPdfTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_upload_a_pdf_file()
    {
        Storage::fake('local');

        $payload = [
            'title' => 'Prueba técnica con PDF',
            'language' => LanguageEnum::PHP->value,
            'description' => 'Descripción de prueba',
            'tags' => ['php', 'laravel'],
            'github_id' => 123456,
        ];

        $file = UploadedFile::fake()->create('prueba.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/technicaltests', array_merge($payload, [
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
