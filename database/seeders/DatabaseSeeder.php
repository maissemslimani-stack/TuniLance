<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use App\Enums\ExperienceLevel;
use App\Enums\ProjectDuration;
use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Categories (matching the landing page grid) ───────────────────
        $categories = collect([
            'Graphic & Design', 'Cartoon & Animation', 'Illustration', 'Web & App Dev',
            'Logo Design', 'Social Graphics', 'Article Writing', 'Video Editing',
        ])->map(fn ($name) => Category::create([
            'name' => $name,
            'slug' => str($name)->slug(),
            'description' => "{$name} services",
            'is_active' => true,
        ]));

        // ─── Predefined skills ───────────────────────────────────────────
        $skills = collect([
            'Laravel', 'Vue.js', 'React', 'Node.js', 'PostgreSQL', 'Tailwind CSS',
            'Figma', 'UI/UX', 'Prototyping', 'User Research', 'Technical Writing', 'SEO',
            'French/English Translation', 'Next.js', 'PHP', 'Python', 'WordPress',
            'Illustration', 'Video Editing', 'Motion Graphics',
        ])->map(fn ($name) => Skill::create([
            'name' => $name,
            'slug' => str($name)->slug(),
        ]));

        // ─── Users ───────────────────────────────────────────────────────
        $verifiedClient = User::factory()->create([
            'name' => 'Ahmed Ben Salem',
            'email' => 'client.verified@tunilance.test',
            'role' => UserRole::Client,
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        $unverifiedClient = User::factory()->create([
            'name' => 'Sarra Mansour',
            'email' => 'client.unverified@tunilance.test',
            'role' => UserRole::Client,
            'is_verified' => false,
        ]);

        $freelancer = User::factory()->create([
            'name' => 'Youssef Trabelsi',
            'email' => 'freelancer@tunilance.test',
            'role' => UserRole::Freelancer,
        ]);

        User::factory()->create([
            'name' => 'Moderator Account',
            'email' => 'moderator@tunilance.test',
            'role' => UserRole::Moderator,
        ]);
        foreach ([$verifiedClient, $unverifiedClient, $freelancer] as $user) {
        $user->wallet()->create(['balance' => 0, 'held_balance' => 0]);
        }
        $freelancer->skills()->attach($skills->whereIn('name', ['Laravel', 'Vue.js', 'PostgreSQL'])->pluck('id'));
        $freelancer->categories()->attach($categories->firstWhere('name', 'Web & App Dev')->id);

        // ─── Projects (mix of verified/unverified clients, various filters) ─
        $seedData = [
            ['E-commerce Platform for Tunisian Artisans', 2500, 4000, ExperienceLevel::Intermediate, ProjectDuration::OneToThreeMonths, $verifiedClient, 'Web & App Dev', ['Laravel', 'PostgreSQL']],
            ['Brand Identity for Tech Startup in Sfax', 1200, 1800, ExperienceLevel::Intermediate, ProjectDuration::LessThanMonth, $verifiedClient, 'Graphic & Design', ['Figma', 'UI/UX']],
            ['Article Content for Travel Blog', 300, 600, ExperienceLevel::Entry, ProjectDuration::LessThanMonth, $unverifiedClient, 'Article Writing', ['Technical Writing', 'SEO']],
            ['Mobile App UI/UX Audit', 800, 1500, ExperienceLevel::Expert, ProjectDuration::OneToThreeMonths, $verifiedClient, 'Web & App Dev', ['UI/UX', 'Prototyping']],
            ['Explainer Video Editing', 400, 900, ExperienceLevel::Intermediate, ProjectDuration::LessThanMonth, $unverifiedClient, 'Video Editing', ['Video Editing', 'Motion Graphics']],
        ];

        foreach ($seedData as [$title, $min, $max, $level, $duration, $client, $catName, $skillNames]) {
            $project = Project::create([
                'client_id' => $client->id,
                'category_id' => $categories->firstWhere('name', $catName)->id,
                'title' => $title,
                'description' => 'Looking for an experienced professional to deliver high-quality work. Full brief shared after proposal.',
                'budget_min' => $min,
                'budget_max' => $max,
                'status' => ProjectStatus::Open,
                'deadline' => now()->addMonths(2),
                'experience_level' => $level,
                'duration' => $duration,
                'proposals_count' => rand(0, 15),
                'published_at' => now()->subHours(rand(1, 48)),
            ]);

            $project->skills()->attach($skills->whereIn('name', $skillNames)->pluck('id'));
        }
        $moreFreelancers = [
    ['Leila Mansouri', 'leila.freelancer@tunilance.test', 'UI/UX Designer & Product Strategist', ['Figma', 'UI/UX', 'Prototyping'], 'Graphic & Design', 5],
    ['Karim Bouzid', 'karim.freelancer@tunilance.test', 'Senior Full-Stack Developer', ['Laravel', 'Vue.js', 'Node.js'], 'Web & App Dev', 6],
    ['Ahmed Trabelsi', 'ahmed.freelancer@tunilance.test', 'Technical Writer & SEO Specialist', ['Technical Writing', 'SEO'], 'Article Writing', 3],
];

foreach ($moreFreelancers as [$name, $email, $headline, $skillNames, $catName, $years]) {
    $f = User::factory()->create([
        'name' => $name,
        'email' => $email,
        'role' => UserRole::Freelancer,
        'headline' => $headline,
        'years_experience' => $years,
    ]);

    $f->wallet()->create(['balance' => 0, 'held_balance' => 0]);
    $f->skills()->attach($skills->whereIn('name', $skillNames)->pluck('id'));
    $f->categories()->attach($categories->firstWhere('name', $catName)->id);
}
    }
}