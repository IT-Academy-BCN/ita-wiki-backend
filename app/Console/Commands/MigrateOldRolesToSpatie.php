<?php

declare (strict_types= 1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\OldRole;

class MigrateOldRolesToSpatie extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spatie:migrate-old-roles {--dry-run : Show what would be migrated}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing OldRole data to Spatie roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting migration from OldRole to Spatie...');
        
        $oldRoles = OldRole::all();
        $migrated = 0;
        $skipped = 0;
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No actual changes will be made');
        }
        
        foreach ($oldRoles as $oldRole) {
            $user = User::where('github_id', $oldRole->github_id)->first();
            
            if ($user) {
                if ($dryRun) {
                    $this->line("📋 Would migrate user {$user->name} (ID: {$user->github_id}) to role {$oldRole->role}");
                } else {
                    // Solo asigna si l'utente non ha già ruoli Spatie
                    if (!$user->hasAnyRole()) {
                        $user->assignRole($oldRole->role);
                        $migrated++;
                        $this->info("✅ Migrated user {$user->name} to role {$oldRole->role}");
                    } else {
                        $this->warn("⚠️  User {$user->name} already has Spatie roles, skipping");
                        $skipped++;
                    }
                }
            } else {
                $this->error("❌ User with github_id {$oldRole->github_id} not found");
            }
        }
        
        if (!$dryRun) {
            $this->info("🎉 Migration completed!");
            $this->info("✅ Migrated: {$migrated} users");
            $this->info("⚠️  Skipped: {$skipped} users");
        }
        
        return Command::SUCCESS;
    }
}
