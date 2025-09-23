<?php

namespace App\Http\Controllers\Tenant\Coach;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Models\Tenant\CoachingContract;
use App\Models\Tenant\CoachingContractSignature;
use App\Services\Tenant\ContractTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Notifications\Tenant\SendContractSigningRequestClient;
use App\Enums\Tenant\ContractStatus;
use App\Notifications\Tenant\ContractFullyExecuted;
class ContractController extends Controller
{
    public function __construct(
        private ContractTemplateService $contractService
    ) {}

    /**
     * Show the contract creation form
     */
    public function create(User $client)
    {
        $this->authorize('create', CoachingContract::class);
        
        return Inertia::render('Tenant/coach/client/ClientContractCreate', [
            'client' => $client,
            'availableTemplates' => $this->contractService->getAvailableTemplates(),
        ]);
    }

    /**
     * Store a new contract
     */
    public function store(Request $request, User $client)
    {
        $this->authorize('create', CoachingContract::class);
        
        $template = $request->input('template_path', 'contracts.standard_coaching_agreement_1');
        
        // Get dynamic validation rules from template schema (excludes dates)
        $validationRules = $this->contractService->getValidationRules($template);
        
        // Add date validation rules (these are now model fields, not template variables)
        $validationRules['start_date'] = 'required|date';
        $validationRules['end_date'] = 'required|date|after:start_date';
        
        $validated = $request->validate($validationRules);

        // Just use the dates as-is
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        
        // Remove dates from validated data as they're handled separately
        unset($validated['start_date'], $validated['end_date']);

        // Create contract using the updated service method
        $contract = $this->contractService->createContract(
            Auth::user(),
            $client,
            $template,
            $startDate,
            $endDate,
            $validated
        );

        return redirect()->route('tenant.coach.clients.contracts.show', [$client, $contract])
            ->with('success', 'Contract created successfully!');
    }

    /**
     * Display the specified contract
     */
    public function show(User $client, CoachingContract $contract)
    {
        // Ensure contract belongs to this client
        if ($contract->client_id !== $client->id) {
            abort(404);
        }
        
        $this->authorize('view', $contract);
        
        $contract->load(['coach', 'client', 'signatures']);
        
        // Get client signature token for sharing link
        $clientSignature = $contract->signatures()->where('signer_id', $client->id)->first();
        $clientSigningToken = $clientSignature?->token;

        // Get coach signature token for coach signing link
        $coachSignature = $contract->signatures()->where('signer_id', $contract->coach_id)->first();
        $coachSigningToken = $coachSignature?->token;

        return Inertia::render('Tenant/coach/client/ClientContractShow', [
            'client' => $client,
            'contract' => [
                'id' => $contract->id,
                'template_path' => $contract->template_path,
                'variables' => $contract->variables,
                'start_date' => $contract->start_date?->format('Y-m-d'),
                'end_date' => $contract->end_date?->format('Y-m-d'),
                'status' => $contract->status->value,
                'created_at' => $contract->created_at->toISOString(),
                'updated_at' => $contract->updated_at->toISOString(),
                'coach_signed' => $contract->isSignedByCoach(),
                'client_signed' => $contract->isSignedByClient(),
                'is_fully_signed' => $contract->isFullySigned(),
                'signing_token' => $clientSigningToken, // Client signing token
                'coach_signing_token' => $coachSigningToken, // Coach signing token
            ],
            'can' => [
                'update' => Auth::user()->can('update', $contract),
                'delete' => Auth::user()->can('delete', $contract),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified contract
     */
    public function edit(User $client, CoachingContract $contract)
    {
        // Ensure contract belongs to this client
        if ($contract->client_id !== $client->id) {
            abort(404);
        }
        
        $this->authorize('update', $contract);
        
        if (!$contract->canBeEdited()) {
            return redirect()->route('tenant.coach.clients.contracts.show', [$client, $contract])
                ->with('error', 'This contract cannot be edited in its current status.');
        }
        
        $contract->load(['coach', 'client']);
        
        return Inertia::render('Tenant/coach/client/ClientContractEdit', [
            'client' => $client,
            'contract' => [
                'id' => $contract->id,
                'template_path' => $contract->template_path,
                'variables' => $contract->variables,
                'start_date' => $contract->start_date?->format('Y-m-d'),
                'end_date' => $contract->end_date?->format('Y-m-d'),
                'status' => $contract->status->value,
                'created_at' => $contract->created_at->toISOString(),
                'updated_at' => $contract->updated_at->toISOString(),
            ],
        ]);
    }

    /**
     * Update the specified contract
     */
    public function update(Request $request, User $client, CoachingContract $contract)
    {
        // Ensure contract belongs to this client
        if ($contract->client_id !== $client->id) {
            abort(404);
        }
        
        $this->authorize('update', $contract);
        
        if (!$contract->canBeEdited()) {
            return back()->with('error', 'This contract cannot be edited in its current status.');
        }
        
        // Get validation rules for this contract's template (excludes dates)
        $validationRules = $this->contractService->getValidationRules($contract->template_path);
        
        // Add date validation rules (these are now model fields, not template variables)
        $validationRules['start_date'] = 'required|date';
        $validationRules['end_date'] = 'required|date|after:start_date';
        
        $validated = $request->validate($validationRules);
        
        // Just use the dates as-is
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        
        // Remove dates from validated data as they're handled separately
        unset($validated['start_date'], $validated['end_date']);
        
        // Update contract variables and dates
        $this->contractService->updateContractVariables($contract, $validated, $startDate, $endDate);
        
        return redirect()->route('tenant.coach.clients.contracts.show', [$client, $contract])
            ->with('success', 'Contract updated successfully!');
    }

    /**
     * Show contract preview (raw HTML for iframe)
     */
    public function preview(User $client, CoachingContract $contract)
    {
        // Ensure contract belongs to this client
        if ($contract->client_id !== $client->id) {
            abort(404);
        }
        
        $this->authorize('view', $contract);
        
        // Ensure contract is rendered
        $this->contractService->ensureContractRendered($contract);
        
        return response($contract->content)
            ->header('Content-Type', 'text/html')
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    /**
     * Show the signature collection page
     */
    public function signature(CoachingContract $contract)
    {
        $this->authorize('view', $contract);
        
        $contract->load(['coach', 'client']);
        
        throw new \Exception('Not implemented');
    }

    /**
     * Save signatures to the contract
     */
    public function saveSignature(Request $request, CoachingContract $contract)
    {
        $this->authorize('update', $contract);
        
        $validated = $request->validate([
            'coach_signature' => 'nullable|string',
            'client_signature' => 'nullable|string',
            'signer_type' => 'required|in:coach,client',
        ]);

        $data = [];
        $currentDate = now()->format('F j, Y');

        if ($validated['signer_type'] === 'coach' && $validated['coach_signature']) {
            $signaturePath = $this->contractService->saveSignature(
                $validated['coach_signature'], 
                'coach_signature'
            );
            $data['coach_signature'] = $signaturePath;
            $data['coach_signature_date'] = $currentDate;
        }

        if ($validated['signer_type'] === 'client' && $validated['client_signature']) {
            $signaturePath = $this->contractService->saveSignature(
                $validated['client_signature'], 
                'client_signature'
            );
            $data['client_signature'] = $signaturePath;
            $data['client_signature_date'] = $currentDate;
        }

        // Re-render the contract with signatures
        $contractData = array_merge(
            $this->contractService->generateContractData($contract->coach, $contract->client),
            $data
        );

        // Add signature URLs for display
        if (isset($data['coach_signature'])) {
            $contractData['coach_signature'] = $this->contractService->getSignatureUrl($data['coach_signature']);
        }
        if (isset($data['client_signature'])) {
            $contractData['client_signature'] = $this->contractService->getSignatureUrl($data['client_signature']);
        }

        $renderedContent = $this->contractService->renderContract(
            'contracts.standard_coaching_agreement_1',
            $contractData
        );

        $contract->update(['content' => $renderedContent]);

        return response()->json([
            'message' => 'Signature saved successfully!',
            'contract' => $contract->fresh(),
        ]);
    }

    /**
     * Generate PDF of the contract
     */
    public function pdf(User $client, CoachingContract $contract)
    {
        $this->authorize('view', $contract);
        
        $pdf = Pdf::loadHTML($contract->content)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

        $nowDateString = now()->format('Y-m-d');
        $filename = 'coaching_contract_' .  $nowDateString . '.pdf';
        
        return $pdf->download($filename);
    }

       /**
     * PDF download by token
     */
    public function pdfToken(string $token)
    {
        $signature = CoachingContractSignature::where('token', $token)->firstOrFail();
        $contract = CoachingContract::with(['coach', 'client'])->findOrFail($signature->contract_id);

        $content = $this->contractService->renderContractFromModel($contract);

        $pdf = Pdf::loadHTML($content)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

        $filename = 'coaching_contract_' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * List all contracts for a specific client
     */
    public function clientContracts(User $client)
    {
        // Ensure the authenticated user can view this client's contracts
        $this->authorize('viewAny', CoachingContract::class);
        
        $contracts = CoachingContract::with(['signatures.signer'])
            ->where('client_id', $client->id)
            //->where('coach_id', Auth::id()) // Only show contracts created by this coach
            ->latest()
            ->get()
            ->map(function ($contract) use ($client) {
                // Get client signature token for sharing link
                $clientSignature = $contract->signatures()->where('signer_id', $client->id)->first();
                
                // Get coach signature token for coach signing link
                $coachSignature = $contract->signatures()->where('signer_id', $contract->coach_id)->first();
                
                return [
                    'id' => $contract->id,
                    'template_path' => $contract->template_path,
                    'variables' => $contract->variables,
                    'start_date' => $contract->start_date?->format('Y-m-d'),
                    'end_date' => $contract->end_date?->format('Y-m-d'),
                    'status' => $contract->status->value,
                    'created_at' => $contract->created_at->toISOString(),
                    'updated_at' => $contract->updated_at->toISOString(),
                    'coach_signed' => $contract->isSignedByCoach(),
                    'client_signed' => $contract->isSignedByClient(),
                    'is_fully_signed' => $contract->isFullySigned(),
                    'signing_token' => $clientSignature?->token, // Client signing token
                    'coach_signing_token' => $coachSignature?->token, // Coach signing token
                    // Add per-contract permissions
                    'can' => [
                        'update' => Auth::user()->can('update', $contract),
                        'delete' => Auth::user()->can('delete', $contract),
                    ],
                ];
            });

        return Inertia::render('Tenant/coach/client/ClientContracts', [
            'client' => $client,
            'contracts' => $contracts,
            'can' => [
                'create' => Auth::user()->can('create', CoachingContract::class),
                'update' => true, // We'll check this per contract in the frontend
                'delete' => true, // We'll check this per contract in the frontend
            ],
        ]);
    }

    /**
     * Get template variables for API
     */
    public function getTemplateVariables(string $templatePath)
    {
        $this->authorize('create', CoachingContract::class);
        
        // Convert dots back to path format
        $templatePath = str_replace('/', '.', $templatePath);
        
        try {
            $variables = $this->contractService->getTemplateVariables($templatePath);
            return response()->json($variables);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Template not found'], 404);
        }
    }

    /**
     * Get available templates for API
     */
    public function getAvailableTemplates()
    {
        $this->authorize('create', CoachingContract::class);
        
        try {
            $templates = $this->contractService->getAvailableTemplates();
            return response()->json($templates);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load templates'], 500);
        }
    }

    /**
     * Delete the specified contract
     */
    public function destroy(User $client, CoachingContract $contract)
    {
        // Ensure contract belongs to this client
        if ($contract->client_id !== $client->id) {
            abort(404);
        }
        
        $this->authorize('delete', $contract);
        
        // Only allow deletion if contract is in draft status
        if (!$contract->canBeDeleted()) {
            return back()->with('error', 'This contract cannot be deleted in its current status.');
        }
        
        $contract->delete();
        
        return redirect()->route('tenant.coach.clients.contracts.index', $client)
            ->with('success', 'Contract deleted successfully!');
    }

    /**
     * Send contract to client
     */
    public function send(User $client, CoachingContract $contract)
    {
        // Ensure contract belongs to this client
        if ($contract->client_id !== $client->id) {
            abort(404);
        }
        
        $this->authorize('update', $contract);
        
        // Only allow sending if contract is in draft status
        if ($contract->status->value !== 0) {
            return back()->with('error', 'This contract has already been sent.');
        }
        
        // Lock template for legal compliance before sending
        $this->contractService->lockContractTemplate($contract);
        
        // Update contract status to sent
        $contract->update(['status' => 1]);

        // Create a pending client signature with token if not exists
        $signature = $contract->signatures()->where('signer_id', $client->id)->first();
        if (!$signature) {
          $signature =    CoachingContractSignature::create([
                'contract_id' => $contract->id,
                'signer_id' => $client->id,
                'token' => Str::random(64),
            ]);
        }
        
        $client->notify(new SendContractSigningRequestClient($contract, $signature));   

        
        return back()->with('success', 'Contract sent to client successfully!');
    }

    /**
     * List all contracts for the authenticated coach
     */
    public function index()
    {
        return redirect()->route('tenant.coach.clients.contracts.index', $client);
    }

    /**
     * Show the coach signing page by token
     */
    public function sign(string $token)
    {
        $signature = CoachingContractSignature::where('token', $token)->firstOrFail();
        $contract = CoachingContract::with(['coach', 'client'])->findOrFail($signature->contract_id);

        // Ensure this is a coach signature token
        if ($signature->signer_id !== $contract->coach_id) {
            abort(404);
        }

        // Render latest HTML for view-only preview inside iframe
        $this->contractService->ensureContractRendered($contract);

        return Inertia::render('Tenant/coach/contracts/SignContract', [
            'token' => $token,
            'contract' => [
                'id' => $contract->id,
                'status' => $contract->status->value,
                'is_fully_signed' => $contract->isFullySigned(),
                'client_signed' => $contract->isSignedByClient(),
            ],
            'client' => [
                'id' => $contract->client->id,
                'name' => $contract->client->name,
                'email' => $contract->client->email,
            ],
            'coach' => [
                'id' => $contract->coach->id,
                'name' => $contract->coach->name,
            ],
            'flash' => [
                'success' => session('success'),
            ],
        ]);
    }

    /**
     * Raw HTML preview for iframe by token (coach signing)
     */
    public function signPreview(string $token)
    {
        $signature = CoachingContractSignature::where('token', $token)->firstOrFail();
        $contract = CoachingContract::with(['coach', 'client'])->findOrFail($signature->contract_id);

        // Ensure this is a coach signature token
        if ($signature->signer_id !== $contract->coach_id) {
            abort(404);
        }

        $content = $this->contractService->renderContractFromModel($contract);

        return response($content)
            ->header('Content-Type', 'text/html')
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    /**
     * Save the coach's signature
     */
    public function storeSignature(Request $request, string $token)
    {
        $signature = CoachingContractSignature::where('token', $token)->firstOrFail();
        $contract = CoachingContract::with(['coach', 'client', 'signatures'])->findOrFail($signature->contract_id);

        // Ensure this is a coach signature token
        if ($signature->signer_id !== $contract->coach_id) {
            abort(404);
        }

        // Prevent clients from signing as coaches
        if (auth()->check() && auth()->id() === $contract->client_id) {
            return back()->withErrors([
                'signature' => 'Clients cannot sign on behalf of coaches. This link is intended for the coach only.'
            ])->withInput();
        }

        $validated = $request->validate([
            'signature' => 'required|string',
        ]);

        // Normalize data URL to raw base64 for template compatibility
        $rawSignature = $validated['signature'];
        if (str_starts_with($rawSignature, 'data:image')) {
            $parts = explode(',', $rawSignature, 2);
            $rawSignature = $parts[1] ?? $rawSignature;
        }

        $signature->update([
            'signature' => $rawSignature,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'signed_at' => now(),
        ]);

        // Update contract status to fully executed if both parties have signed
        if ($contract->isSignedByClient() && $signature->signature) {
            $contract->update(['status' => ContractStatus::ACTIVE]);
            
            // TODO: Send ContractFullyExecuted notification to both parties
            $contract->client->notify(new ContractFullyExecuted($contract, $signature));
            $contract->coach->notify(new ContractFullyExecuted($contract, $signature));
        }

        // Re-render and persist content to include signatures
        $content = $this->contractService->renderContractFromModel($contract);
        $contract->update(['content' => $content]);

        return to_route('tenant.coach.contracts.sign', ['token' => $token])->with('success', 'Signature saved successfully');
    }
}
