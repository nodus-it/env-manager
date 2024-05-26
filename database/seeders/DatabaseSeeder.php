<?php

namespace Database\Seeders;

use App\Models\Enums\ProjectStatus;
use App\Models\Enums\UserStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Bastian Schur',
            'email' => 'b.schur@nodus-it.de',
            'status' => UserStatus::ACTIVE,
            'password' => Hash::make(12345),
        ]);

        User::create([
            'name' => 'Test',
            'email' => 'test@test.de',
            'status' => UserStatus::INACTIVE,
            'password' => Hash::make(12345),
        ]);

        Project::create([
            'name' => 'ENV-Manager',
            'description' => 'Der ENV-Manager verwaltet Secrets für ENV-Dateien um eine systemübergreifende Synchronisation zu ermöglichen',
            'status' => ProjectStatus::ACTIVE,
        ]);

        Project::create([
            'name' => 'Nexum',
            'description' => 'Nexum ist ein CRM für den Finanzsektor',
            'status' => ProjectStatus::INACTIVE,
        ]);

        Project::create([
            'name' => 'Nodus Framework',
            'description' => 'Nodus Framework ist eine Laravel-Erweiterung zur einfachen Gestaltung von Backend-Systmen',
            'status' => ProjectStatus::INACTIVE,
        ]);

        Project::create([
            'name' => 'MainApi',
            'description' => 'Das Projekt ist eine Leadstrecke inklusive API Anbindung an Prohyp',
            'status' => ProjectStatus::ARCHIVED,
        ]);
    }
}
