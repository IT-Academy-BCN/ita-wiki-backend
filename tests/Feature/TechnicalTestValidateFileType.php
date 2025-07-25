<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TechnicalTestValidateFileType extends TestCase
{
    use RefreshDatabase;

    /** @test */
public function it_cannot_upload_a_non_pdf_file()
    {
        Storage::fake('local');

        $payload = [
            'title' => 'Prueba técnica con archivo no PDF',
            'language' => 'PHP',
            'description' => 'Descripción de prueba',
            'tags' => ['php', 'laravel'],
            'github_id' => 123456,
        ];

        $file = UploadedFile::fake()->create('prueba.png', 100, 'image/png');

        $response = $this->postJson('/api/technicaltests', array_merge($payload, [
            'file' => $file,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
        
        $this->assertDatabaseMissing('technical_tests', [
            'title' => 'Prueba técnica con archivo no PDF',
        ]);

        $this->assertFalse(Storage::disk('local')->exists('technical_tests/prueba.png'));
    }

}