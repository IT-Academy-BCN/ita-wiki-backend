<?php

declare (strict_types= 1);

namespace App\Console\Commands;

use App\Models\Role;
use Illuminate\Console\Command;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:superadmin {github_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Superadmin with a given env variable';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        
        $superadmin_github_id = $this->argument('github_id');

        if(!$superadmin_github_id){
            $superadmin_github_id = env('SUPERADMIN_GITHUB_ID');
        }

        if(!$superadmin_github_id){
            $this->error('Github id not defined');
            return Command::FAILURE;
        }

        Role::create([
            'github_id' => $superadmin_github_id,
            'role' => 'superadmin',
        ]);

        $this->info('Superusuario creado correctamente con github_id: ' . $superadmin_github_id);
        return Command::SUCCESS;
    }
}
