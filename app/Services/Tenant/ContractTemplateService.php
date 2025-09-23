<?php

namespace App\Services\Tenant;

use App\Models\Tenant\User;
use App\Models\Tenant\CoachingContract;
use App\Services\Tenant\LogoService;
use Carbon\Carbon;

class ContractTemplateService
{
    protected LogoService $logoService;

    public function __construct(LogoService $logoService)
    {
        $this->logoService = $logoService;
    }
    /**
     * Render contract template with data using Blade
     */
    public function renderContract(string $templatePath, array $data): string
    {
        // Let Blade handle the variable interpolation
        return view($templatePath, $data)->render();
    }

    /**
     * Render contract with coach and client models
     */
    public function renderContractWithModels(string $templatePath, User $coach, User $client, array $variables = [], ?CoachingContract $contract = null): string
    {
        $data = array_merge($variables, [
            'coach' => $coach,
            'client' => $client,
            'logo' => $this->logoService->getContractLogoData(),
        ]);

        // If we have a contract model, include the dates from it
        // Convert UTC dates back to a reasonable display format
        // Since contracts are date-based (not time-based), we'll show the date portion
        if ($contract) {
            $data['start_date'] = $contract->start_date ? $contract->start_date->format('F j, Y') : null;
            $data['end_date'] = $contract->end_date ? $contract->end_date->format('F j, Y') : null;
        }

        return view($templatePath, $data)->render();
    }

    /**
     * Generate contract data array from coach, client, and contract details
     */
    public function generateContractData(User $coach, User $client, array $contractDetails = []): array
    {
        $currentDate = Carbon::now();
        
        return array_merge([
            // Contract metadata
            'contract_date' => $contractDetails['contract_date'] ?? $currentDate->format('F j, Y'),
            'start_date' => $contractDetails['start_date'] ?? $currentDate->format('F j, Y'),
            'end_date' => $contractDetails['end_date'] ?? $currentDate->addMonths(3)->format('F j, Y'),
            
            // Coach information
            'coach_name' => $coach->name,
            'coach_email' => $coach->email,
            'coach_phone' => $coach->phone ?? 'Not provided',
            
            // Client information
            'client_name' => $client->name,
            'client_email' => $client->email,
            'client_phone' => $client->phone ?? 'Not provided',
            
            // Session details (with defaults)
            'session_frequency' => $contractDetails['session_frequency'] ?? 'Weekly',
            'session_duration' => $contractDetails['session_duration'] ?? '60',
            'session_format' => $contractDetails['session_format'] ?? 'Online',
            'session_location' => $contractDetails['session_location'] ?? 'Online Platform',
            'total_sessions' => $contractDetails['total_sessions'] ?? '12',
            
            // Financial terms
            'session_fee' => $contractDetails['session_fee'] ?? '$100',
            'package_fee' => $contractDetails['package_fee'] ?? '$1,200',
            'payment_terms' => $contractDetails['payment_terms'] ?? 'Monthly in advance',
            
            // Signature data (will be populated when signatures are collected)
            'coach_signature' => $contractDetails['coach_signature'] ?? null,
            'client_signature' => $contractDetails['client_signature'] ?? null,
            'coach_signature_date' => $contractDetails['coach_signature_date'] ?? null,
            'client_signature_date' => $contractDetails['client_signature_date'] ?? null,
        ], $contractDetails);
    }

    // Signature handling moved to dedicated service/controller
    // Signatures are now stored encrypted in database via CoachingContractSignature model

    /**
     * Create and save a coaching contract with template snapshot
     */
    public function createContract(User $coach, User $client, string $templatePath, string $startDate, string $endDate, array $contractDetails = []): CoachingContract
    {
        // Only store user-input variables, not auto-populated data (excluding dates)
        $variables = $this->filterUserInputVariables($templatePath, $contractDetails);
        
        // Create template snapshot for legal compliance
        $templateSnapshot = $this->createTemplateSnapshot($templatePath);
        $templateVersion = $this->getTemplateVersion($templatePath);
        
        return CoachingContract::create([
            'coach_id' => $coach->id,
            'client_id' => $client->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'template_path' => $templatePath,
            'template_snapshot' => $templateSnapshot,
            'template_version' => $templateVersion,
            'template_snapshot_at' => now(),
            'variables' => $variables,
            'content' => null, // Will be rendered when needed
        ]);
    }

    /**
     * Filter out auto-populated variables, keep only user input (excluding dates which are now model fields)
     */
    private function filterUserInputVariables(string $templatePath, array $contractDetails): array
    {
        try {
            $schema = $this->extractTemplateSchema($templatePath);
            $userInputVariables = [];
            
            foreach ($schema['categories'] ?? [] as $categoryKey => $category) {
                // Skip auto-populated categories
                if ($category['auto_populate'] ?? false) {
                    continue;
                }
                
                foreach ($category['fields'] ?? [] as $fieldKey => $field) {
                    // Skip date fields as they're now model fields, not variables
                    if (in_array($fieldKey, ['start_date', 'end_date'])) {
                        continue;
                    }
                    
                    if (isset($contractDetails[$fieldKey])) {
                        $userInputVariables[$fieldKey] = $contractDetails[$fieldKey];
                    } elseif (isset($field['default'])) {
                        $userInputVariables[$fieldKey] = $field['default'];
                    }
                }
            }
            
            return $userInputVariables;
        } catch (\Exception $e) {
            // Fallback: return all provided details, excluding dates
            $filtered = $contractDetails;
            unset($filtered['start_date'], $filtered['end_date']);
            return $filtered;
        }
    }

    /**
     * Render contract content from stored variables using template snapshot
     */
    public function renderContractFromModel(CoachingContract $contract): string
    {
        // Include signature models if they exist, plus contract creation date and dates from model
        // Note: For contract display, we show dates as they would appear to the user
        // Since we stored start/end of day in user's timezone as UTC, we need to format them appropriately
        $additionalData = [
            'coachSignature' => $contract->coachSignature(),
            'clientSignature' => $contract->clientSignature(),
            'contract_date' => $contract->created_at->format('F j, Y'),
            'start_date' => $contract->start_date ? $contract->start_date->format('F j, Y') : null,
            'end_date' => $contract->end_date ? $contract->end_date->format('F j, Y') : null,
        ];

        // Use template snapshot for legal compliance if contract is signed
        if ($contract->status->value >= 2 && $contract->template_snapshot) {
            return $this->renderFromSnapshot(
                $contract->template_snapshot,
                $contract->coach,
                $contract->client,
                array_merge($contract->variables ?? [], $additionalData)
            );
        }

        // Use current template for draft contracts (allows editing)
        return $this->renderContractWithModels(
            $contract->template_path,
            $contract->coach,
            $contract->client,
            array_merge($contract->variables ?? [], $additionalData),
            $contract
        );
    }

    /**
     * Update contract variables and clear rendered content
     */
    public function updateContractVariables(CoachingContract $contract, array $newVariables, ?string $startDate = null, ?string $endDate = null): void
    {
        if (!$contract->canBeEdited()) {
            throw new \Exception('Contract cannot be edited in current status: ' . $contract->status->label());
        }

        // Filter out date fields from variables as they're now model fields
        $filteredVariables = $newVariables;
        unset($filteredVariables['start_date'], $filteredVariables['end_date']);

        // Merge with existing variables
        $variables = array_merge($contract->variables ?? [], $filteredVariables);
        
        $updateData = [
            'variables' => $variables,
            'content' => null, // Clear rendered content to force re-render
        ];

        // Update dates if provided
        if ($startDate) {
            $updateData['start_date'] = $startDate;
        }
        if ($endDate) {
            $updateData['end_date'] = $endDate;
        }
        
        $contract->update($updateData);
    }

    /**
     * Ensure contract content is rendered and up to date
     */
    public function ensureContractRendered(CoachingContract $contract): void
    {
        if ($contract->needsRerendering()) {
            $content = $this->renderContractFromModel($contract);
            $contract->update(['content' => $content]);
        }
    }

    /**
     * Extract template schema from blade template file
     */
    public function extractTemplateSchema(string $templatePath): array
    {
        // Get the full file path for the template
        $fullPath = resource_path('views/' . str_replace('.', '/', $templatePath) . '.blade.php');
        
        if (!file_exists($fullPath)) {
            throw new \Exception("Template file not found: {$fullPath}");
        }
        
        $content = file_get_contents($fullPath);
        
        // Extract schema from template comments
        $pattern = '/{{--\s*@template-schema\s*(.*?)\s*@end-template-schema\s*--}}/s';
        
        if (preg_match($pattern, $content, $matches)) {
            $schemaJson = trim($matches[1]);
            $schema = json_decode($schemaJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON in template schema: " . json_last_error_msg());
            }
            
            return $schema;
        }
        
        throw new \Exception("No template schema found in template: {$templatePath}");
    }

    /**
     * Get available template variables for form generation (dynamic from template)
     */
    public function getTemplateVariables(string $templatePath = 'contracts.standard_coaching_agreement_1'): array
    {
        try {
            $schema = $this->extractTemplateSchema($templatePath);
            return $schema['categories'] ?? [];
        } catch (\Exception $e) {
            // Fallback to static variables if schema extraction fails
            return $this->getFallbackTemplateVariables();
        }
    }

    /**
     * Get template metadata (name, version, etc.)
     */
    public function getTemplateMetadata(string $templatePath): array
    {
        try {
            $schema = $this->extractTemplateSchema($templatePath);
            return [
                'name' => $schema['template_name'] ?? 'Unknown Template',
                'version' => $schema['template_version'] ?? '1.0',
                'categories_count' => count($schema['categories'] ?? []),
                'total_fields' => $this->countTotalFields($schema['categories'] ?? []),
            ];
        } catch (\Exception $e) {
            return [
                'name' => 'Unknown Template',
                'version' => '1.0',
                'categories_count' => 0,
                'total_fields' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate form validation rules from template schema (excluding date fields which are now model fields)
     */
    public function getValidationRules(string $templatePath): array
    {
        try {
            $schema = $this->extractTemplateSchema($templatePath);
            $rules = [];
            
            foreach ($schema['categories'] ?? [] as $categoryKey => $category) {
                // Skip auto-populated categories
                if ($category['auto_populate'] ?? false) {
                    continue;
                }
                
                foreach ($category['fields'] ?? [] as $fieldKey => $field) {
                    // Skip date fields as they're now model fields, not form variables
                    if (in_array($fieldKey, ['start_date', 'end_date'])) {
                        continue;
                    }
                    
                    $fieldRules = [];
                    
                    // Required validation
                    if ($field['required'] ?? false) {
                        $fieldRules[] = 'required';
                    } else {
                        $fieldRules[] = 'nullable';
                    }
                    
                    // Type-specific validations
                    switch ($field['type']) {
                        case 'date':
                            $fieldRules[] = 'date';
                            break;
                        case 'email':
                            $fieldRules[] = 'email';
                            break;
                        case 'number':
                            $fieldRules[] = 'integer';
                            if (isset($field['min'])) {
                                $fieldRules[] = 'min:' . $field['min'];
                            }
                            if (isset($field['max'])) {
                                $fieldRules[] = 'max:' . $field['max'];
                            }
                            break;
                        case 'select':
                            if (isset($field['options'])) {
                                $fieldRules[] = 'in:' . implode(',', $field['options']);
                            }
                            break;
                        case 'currency':
                            $fieldRules[] = 'string';
                            break;
                        case 'phone':
                            $fieldRules[] = 'string';
                            break;
                        default:
                            $fieldRules[] = 'string';
                    }
                    
                    $rules[$fieldKey] = implode('|', $fieldRules);
                }
            }
            
            return $rules;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Generate contract data with schema-aware defaults
     */
    public function generateContractDataFromSchema(User $coach, User $client, array $contractDetails = [], string $templatePath = 'contracts.standard_coaching_agreement_1'): array
    {
        try {
            $schema = $this->extractTemplateSchema($templatePath);
            $data = [];
            $currentDate = Carbon::now();
            
            foreach ($schema['categories'] ?? [] as $categoryKey => $category) {
                foreach ($category['fields'] ?? [] as $fieldKey => $field) {
                    $value = null;
                    
                    // Check if value provided in contractDetails
                    if (isset($contractDetails[$fieldKey])) {
                        $value = $contractDetails[$fieldKey];
                    }
                    // Auto-populate from source (coach/client)
                    elseif ($category['auto_populate'] ?? false) {
                        $source = $category['source'] === 'coach' ? $coach : $client;
                        $sourceField = $field['source_field'] ?? $fieldKey;
                        $value = $source->{$sourceField} ?? null;
                    }
                    // Use default value from schema
                    elseif (isset($field['default'])) {
                        $default = $field['default'];
                        if ($default === 'current_date') {
                            $value = $currentDate->format('F j, Y');
                        } else {
                            $value = $default;
                        }
                    }
                    
                    // Handle special auto-fill cases
                    if (isset($field['auto_fill']) && $field['auto_fill'] === 'signature_date') {
                        // This will be filled when signature is collected
                        $value = $contractDetails[$fieldKey] ?? null;
                    }
                    
                    $data[$fieldKey] = $value;
                }
            }
            
            return $data;
        } catch (\Exception $e) {
            // Fallback to original method
            return $this->generateContractData($coach, $client, $contractDetails);
        }
    }

    /**
     * Count total fields in schema categories
     */
    private function countTotalFields(array $categories): int
    {
        $count = 0;
        foreach ($categories as $category) {
            $count += count($category['fields'] ?? []);
        }
        return $count;
    }

    /**
     * Get all available contract templates
     */
    public function getAvailableTemplates(): array
    {
        $contractsPath = resource_path('views/contracts');
        
        if (!is_dir($contractsPath)) {
            return [];
        }
        
        $templates = [];
        $files = glob($contractsPath . '/*.blade.php');
        
        foreach ($files as $file) {
            $filename = basename($file, '.blade.php');
            $templatePath = 'contracts.' . $filename;
            
            try {
                $content = file_get_contents($file);
                
                // Extract title from HTML
                $title = $this->extractTitleFromTemplate($content);
                
                // Try to extract schema for additional metadata
                $schema = null;
                try {
                    $schema = $this->extractTemplateSchema($templatePath);
                } catch (\Exception $e) {
                    // Schema is optional, continue without it
                }
                
                $templates[] = [
                    'path' => $templatePath,
                    'filename' => $filename,
                    'title' => $title ?: $this->generateTitleFromFilename($filename),
                    'description' => $schema['template_description'] ?? null,
                    'version' => $schema['template_version'] ?? '1.0',
                    'categories_count' => count($schema['categories'] ?? []),
                    'total_fields' => $this->countTotalFields($schema['categories'] ?? []),
                ];
                
            } catch (\Exception $e) {
                // Skip templates that can't be read
                continue;
            }
        }
        
        return $templates;
    }

    /**
     * Extract title from template HTML content
     */
    private function extractTitleFromTemplate(string $content): ?string
    {
        // Look for <title> tag
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $content, $matches)) {
            return trim(strip_tags($matches[1]));
        }
        
        return null;
    }

    /**
     * Generate a readable title from filename
     */
    private function generateTitleFromFilename(string $filename): string
    {
        // Convert snake_case or kebab-case to Title Case
        $title = str_replace(['_', '-'], ' ', $filename);
        $title = ucwords($title);
        
        // Add "Agreement" if not already present
        if (!str_contains(strtolower($title), 'agreement') && !str_contains(strtolower($title), 'contract')) {
            $title .= ' Agreement';
        }
        
        return $title;
    }

    /**
     * Get template info by path
     */
    public function getTemplateInfo(string $templatePath): array
    {
        $templates = $this->getAvailableTemplates();
        
        foreach ($templates as $template) {
            if ($template['path'] === $templatePath) {
                return $template;
            }
        }
        
        throw new \Exception("Template not found: {$templatePath}");
    }

    /**
     * Create template snapshot for legal compliance
     */
    private function createTemplateSnapshot(string $templatePath): string
    {
        $fullPath = resource_path('views/' . str_replace('.', '/', $templatePath) . '.blade.php');
        
        if (!file_exists($fullPath)) {
            throw new \Exception("Template file not found: {$fullPath}");
        }
        
        return file_get_contents($fullPath);
    }

    /**
     * Get template version from schema or generate one
     */
    private function getTemplateVersion(string $templatePath): string
    {
        try {
            $schema = $this->extractTemplateSchema($templatePath);
            return $schema['template_version'] ?? '1.0';
        } catch (\Exception $e) {
            // Generate version based on file modification time
            $fullPath = resource_path('views/' . str_replace('.', '/', $templatePath) . '.blade.php');
            if (file_exists($fullPath)) {
                return date('Y.m.d.H.i', filemtime($fullPath));
            }
            return '1.0';
        }
    }

    /**
     * Render contract from template snapshot
     */
    private function renderFromSnapshot(string $templateSnapshot, User $coach, User $client, array $variables = []): string
    {
        // Create a temporary view file from the snapshot
        $tempPath = storage_path('app/temp_templates');
        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0755, true);
        }
        
        $tempFile = $tempPath . '/contract_' . uniqid() . '.blade.php';
        file_put_contents($tempFile, $templateSnapshot);
        
        try {
            // Prepare data for rendering (dates should already be in variables array)
            $data = array_merge($variables, [
                'coach' => $coach,
                'client' => $client,
                'logo' => $this->logoService->getContractLogoData(),
            ]);

            // Use Laravel's view system to render the snapshot
            $viewContent = view()->file($tempFile, $data)->render();
            
            return $viewContent;
        } finally {
            // Clean up temporary file
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    /**
     * Update contract to use template snapshot (when contract is sent/signed)
     */
    public function lockContractTemplate(CoachingContract $contract): void
    {
        if (!$contract->template_snapshot) {
            $templateSnapshot = $this->createTemplateSnapshot($contract->template_path);
            $templateVersion = $this->getTemplateVersion($contract->template_path);
            
            $contract->update([
                'template_snapshot' => $templateSnapshot,
                'template_version' => $templateVersion,
                'template_snapshot_at' => now(),
            ]);
        }
    }

    /**
     * Check if contract is using template snapshot
     */
    public function isUsingSnapshot(CoachingContract $contract): bool
    {
        return !empty($contract->template_snapshot) && $contract->status->value >= 2;
    }

    /**
     * Fallback template variables (original static method)
     */
    private function getFallbackTemplateVariables(): array
    {
        return [
            'contract_details' => [
                'start_date' => 'Contract Start Date',
                'end_date' => 'Contract End Date',
            ],
            'session_details' => [
                'session_frequency' => 'Session Frequency (e.g., Weekly, Bi-weekly)',
                'session_duration' => 'Session Duration (minutes)',
                'session_format' => 'Session Format (Online, In-person, Phone)',
                'session_location' => 'Session Location',
                'total_sessions' => 'Total Number of Sessions',
            ],
            'financial_terms' => [
                'session_fee' => 'Fee per Session',
                'package_fee' => 'Total Package Fee',
                'payment_terms' => 'Payment Terms',
            ],
        ];
    }
}
