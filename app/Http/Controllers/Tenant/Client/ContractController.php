<?php

namespace App\Http\Controllers\Tenant\Client;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CoachingContract;
use App\Models\Tenant\CoachingContractSignature;
use App\Services\Tenant\ContractTemplateService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Enums\Tenant\ContractStatus;
use App\Notifications\Tenant\SendContractSigningRequestCoach;
class ContractController extends Controller
{
    public function __construct(
        private ContractTemplateService $contractService
    ) {}

    /**
     * Show the public signing page by token
     */
    public function sign(string $token)
    {
        $signature = CoachingContractSignature::where('token', $token)->firstOrFail();
        $contract = CoachingContract::with(['coach', 'client'])->findOrFail($signature->contract_id);

        // Render latest HTML for view-only preview inside iframe
        $this->contractService->ensureContractRendered($contract);

        return Inertia::render('Tenant/client/contracts/SignContract', [
            'token' => $token,
            'contract' => [
                'id' => $contract->id,
                'status' => $contract->status->value,
                'is_fully_signed' => $contract->isFullySigned(),
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
     * Raw HTML preview for iframe by token
     */
    public function preview(string $token)
    {
        $signature = CoachingContractSignature::where('token', $token)->firstOrFail();
        $contract = CoachingContract::with(['coach', 'client'])->findOrFail($signature->contract_id);

        $content = $this->contractService->renderContractFromModel($contract);

        return response($content)
            ->header('Content-Type', 'text/html')
            ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    /**
     * Save the client's signature
     */
    public function store(Request $request, string $token)
    {
        $signature = CoachingContractSignature::where('token', $token)->firstOrFail();
        $contract = CoachingContract::with(['coach', 'client', 'signatures'])->findOrFail($signature->contract_id);

        // Prevent coaches from signing as clients
        if (auth()->check() && auth()->id() === $contract->coach_id) {
            return back()->withErrors([
                'signature' => 'Coaches cannot sign on behalf of clients. Please share this link with your client instead.'
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

        // Update contract status to signed by client if applicable
        $contract->update(['status' => ContractStatus::SIGNED_CLIENT]);

        // Create coach signature token if not exists
        $coachSignature = $contract->signatures()->where('signer_id', $contract->coach_id)->first();
        if (!$coachSignature) {
            $coachSignature = CoachingContractSignature::create([
                'contract_id' => $contract->id,
                'signer_id' => $contract->coach_id,
                'token' => Str::random(64),
            ]);
        }

        // Send notification to coach that client has signed and they need to countersign
        $contract->coach->notify(new SendContractSigningRequestCoach($contract, $coachSignature));

        // Re-render and persist content to include signatures
        $content = $this->contractService->renderContractFromModel($contract);
        $contract->update(['content' => $content]);

        return to_route('tenant.contracts.sign', ['token' => $token])->with('success', 'Signature saved successfully');
    }

    /**
     * PDF download by token
     */
    public function pdf(string $token)
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
}
