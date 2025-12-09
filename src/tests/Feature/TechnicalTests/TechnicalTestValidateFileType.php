<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Enums\LanguageEnum;

class TechnicalTestValidateFileType extends TestCase
{
    use RefreshDatabase;

    public function test_it_cannot_upload_a_non_pdf_file()
    {
        Storage::fake('local');

        $user = $this->authenticateUserWithRole('mentor');

        $payload = [
            'title' => 'Prueba técnica con archivo no PDF',
            'language' => LanguageEnum::PHP->value,
            'description' => 'Descripción de prueba',
            'tags' => ['php', 'laravel'],
        ];

        $file = UploadedFile::fake()->create('prueba.png', 100, 'image/png');

        $response = $this->postJson(route('technical-tests.store'), array_merge($payload, [
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