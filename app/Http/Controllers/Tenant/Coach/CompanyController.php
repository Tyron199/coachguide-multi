<?php

namespace App\Http\Controllers\Tenant\Coach;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Company;
use App\Enums\Tenant\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Authorize viewing companies list
        $this->authorize('viewAny', Company::class);
        

        
        $query = Company::withCount('users');
        
        // Scope companies based on user role
        if (auth()->user()->hasRole('coach') && !auth()->user()->hasRole('admin')) {
            // Regular coaches only see companies where they have assigned clients
            $query->whereHas('users', function($q) {
                $q->where('assigned_coach_id', auth()->id());
            });
        }
        // Admins see all companies (no additional filtering needed)
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('contact_person_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('contact_person_email', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Sorting functionality
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        // Validate sort direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }
        
        // Handle different sort columns
        switch ($sortBy) {
            case 'name':
                $query->orderBy('companies.name', $sortDirection);
                break;
            case 'contact_person':
                $query->orderBy('companies.contact_person_name', $sortDirection);
                break;
            case 'contact_email':
                $query->orderBy('companies.contact_person_email', $sortDirection);
                break;
            case 'clients_count':
                $query->orderBy('users_count', $sortDirection);
                break;
            case 'created_at':
                $query->orderBy('companies.created_at', $sortDirection);
                break;
            default:
                $query->orderBy('companies.name', $sortDirection);
                break;
        }
        
        $companies = $query->paginate(config('app.pagination_limit'));
        
        return Inertia::render("Tenant/coach/companies/ListCompanies", [
            'companies' => $companies,
            'filters' => [
                'search' => $request->search,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Authorize company creation
        $this->authorize('create', Company::class);
        
            return Inertia::render('Tenant/coach/companies/CreateCompany');
        }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Authorize company creation
        $this->authorize('create', Company::class);
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:companies',
            'address' => 'nullable|string|max:500',
            'industry_sector' => 'nullable|string|max:255',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_position' => 'nullable|string|max:255',
            'contact_person_email' => 'nullable|email|max:255',
            'contact_person_phone' => 'nullable|string|max:50',
            'billing_contact_name' => 'nullable|string|max:255',
            'billing_contact_email' => 'nullable|email|max:255',
            'billing_contact_phone' => 'nullable|string|max:50',
            'invoicing_notes' => 'nullable|string|max:1000',
        ]);

        Company::create($validatedData);

        return to_route('tenant.companies.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        // Authorize viewing this company
        $this->authorize('view', $company);
        
        $company->loadCount('users');
        $company->load(['users' => function($query) {
            $query->orderBy('name');
        }]);
        
        return Inertia::render('Tenant/coach/company/CompanyShow', [
            'company' => $company
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        // Authorize editing this company
        $this->authorize('update', $company);
        
        return Inertia::render('Tenant/coach/company/CompanyEdit', [
            'company' => $company
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        // Authorize updating this company
        $this->authorize('update', $company);
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:companies,name,' . $company->id,
            'address' => 'nullable|string|max:500',
            'industry_sector' => 'nullable|string|max:255',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_position' => 'nullable|string|max:255',
            'contact_person_email' => 'nullable|email|max:255',
            'contact_person_phone' => 'nullable|string|max:50',
            'billing_contact_name' => 'nullable|string|max:255',
            'billing_contact_email' => 'nullable|email|max:255',
            'billing_contact_phone' => 'nullable|string|max:50',
            'invoicing_notes' => 'nullable|string|max:1000',
        ]);

        $company->update($validatedData);
        
        return to_route('tenant.companies.show', $company)->with('success', 'Company updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        // Authorize deleting this company
        $this->authorize('delete', $company);
        
        // Check if company has users
        if ($company->users()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete company with associated clients'
            ], 422);
        }
        
        $company->delete();
        
        return to_route('tenant.companies.index')->with('success', 'Company deleted successfully');
    }

    /**
     * Delete batch of companies.
     */
    public function destroyBatch(Request $request)
    {
        $request->validate([
            'companies' => 'required|array',
            'companies.*' => 'exists:companies,id',
        ]);
        
        $companies = Company::whereIn('id', $request->companies)->get();
        
        foreach ($companies as $company) {
            $this->authorize('delete', $company);
            $company->delete();
        }

        return to_route('tenant.companies.index')->with('success', 'Companies deleted successfully');
    }

    public function employees(Company $company)
    {
        // Authorize viewing employees of this company
        $this->authorize('viewEmployees', $company);
        
        $query = $company->users()->role(UserRole::CLIENT)->with(['company', 'assignedCoach'])->where('archived', false);
        
        // Scope employees based on user role
        if (auth()->user()->hasRole('coach') && !auth()->user()->hasRole('admin')) {
            // Regular coaches only see their assigned clients within this company
            $query->where('assigned_coach_id', auth()->id());
        }
        // Admins see all employees of the company (no additional filtering needed)
        
        // Search functionality
        if (request()->has('search') && request()->search) {
            $searchTerm = request()->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }
        
        $clients = $query->orderBy('name')->paginate(10);
        
        // Transform to match the expected format for ClientsTable
        $paginatedClients = [
            'data' => $clients->items(),
            'current_page' => $clients->currentPage(),
            'last_page' => $clients->lastPage(),
            'per_page' => $clients->perPage(),
            'total' => $clients->total(),
        ];

        return Inertia::render('Tenant/coach/company/CompanyEmployees', [
            'company' => $company,
            'clients' => $paginatedClients,
            'companies' => [$company], // Only this company for the filter
            'filters' => [
                'search' => request()->search,
                'company_id' => $company->id,
                'archived' => false
            ],
            'canSeeCoachColumn' => auth()->user()->hasRole('admin')
        ]);
    }
}
