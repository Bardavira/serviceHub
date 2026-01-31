<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        Project::create([
            'company_id' => $company->id,
            'name' => 'Project 1',
        ]);

        Project::create([
            'company_id' => $company->id,
            'name' => 'Project 2',
        ]);
    }
}
