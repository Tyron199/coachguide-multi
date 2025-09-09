<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\Tenant\CoachingFramework;
use Illuminate\Support\Facades\File;

class CoachingFrameworkSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // Load Models
        $this->loadFrameworksFromDirectory('models');
        
        // Load Tools
        $this->loadFrameworksFromDirectory('tools');
    }
    
    private function loadFrameworksFromDirectory(string $directory): void
    {
        $path = resource_path("frameworks/{$directory}");
        
        if (!File::exists($path)) {
            $this->command->warn("Directory {$path} does not exist");
            return;
        }
        
        $files = File::files($path);
        
        foreach ($files as $file) {
            if ($file->getExtension() === 'json') {
                $this->loadFrameworkFromFile($file->getPathname());
            }
        }
    }
    
    private function loadFrameworkFromFile(string $filePath): void
    {
        try {
            $content = File::get($filePath);
            $framework = json_decode($content, true);
            
            if (!$framework) {
                $this->command->error("Failed to parse JSON from {$filePath}");
                return;
            }
            
            // Extract metadata
            $slug = $framework['slug'] ?? null;
            $name = $framework['name'] ?? null;
            $description = $framework['description'] ?? null;
            $category = $framework['category'] ?? null;
            $subcategory = $framework['subcategory'] ?? null;
            $bestFor = $framework['bestFor'] ?? [];
            
            if (!$slug) {
                $this->command->error("Framework missing slug in {$filePath}");
                return;
            }
            
            // Check if framework exists
            $existing = CoachingFramework::where('slug', $slug)->first();
            
            // Remove metadata from schema
            unset($framework['slug'], $framework['name'], $framework['description'], 
                  $framework['category'], $framework['subcategory'], $framework['bestFor']);
            
            // Create schema hash for change detection
            $schemaHash = md5(json_encode($framework));
            
            if ($existing) {
                // Check if schema has changed
                $existingHash = md5(json_encode($existing->schema));
                
                if ($existingHash === $schemaHash) {
                    $this->command->line("  Skipped: {$name} (no changes)");
                    return;
                }
                
                // Update existing framework
                $existing->update([
                    'name' => $name,
                    'description' => $description,
                    'category' => $category,
                    'subcategory' => $subcategory,
                    'best_for' => $bestFor,
                    'schema' => $framework,
                ]);
                
                $this->command->info("  Updated: {$name}");
            } else {
                // Create new framework
                CoachingFramework::create([
                    'slug' => $slug,
                    'name' => $name,
                    'description' => $description,
                    'category' => $category,
                    'subcategory' => $subcategory,
                    'best_for' => $bestFor,
                    'schema' => $framework,
                    'is_system' => true,
                    'created_by_coach_id' => null,
                    'is_active' => true,
                ]);
                
                $this->command->info("  Created: {$name}");
            }
            
        } catch (\Exception $e) {
            $this->command->error("Error loading {$filePath}: " . $e->getMessage());
        }
    }
}