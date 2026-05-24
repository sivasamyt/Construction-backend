<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\RegisterCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\DomainResource;
use App\Models\Company;
use App\Services\CompanyService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly CompanyService $companyService) {}

    public function previewDomain(Request $request): JsonResponse
    {
        $request->validate(['domain' => ['required', 'string', 'max:255']]);

        return $this->success(
            $this->companyService->previewDomain($request->query('domain')),
            'Domain preview generated'
        );
    }

    public function register(RegisterCompanyRequest $request): JsonResponse
    {
        $result = $this->companyService->register($request->validated());

        return $this->success([
            'company' => new CompanyResource($result['company']),
            'domain' => new DomainResource($result['domain']),
            'url' => $result['url'],
        ], 'Company registered successfully', 201);
    }

    public function index(Request $request): JsonResponse
    {
        $companies = $this->companyService->list($request->only(['search', 'per_page']));

        return response()->json([
            'success' => true,
            'message' => 'Companies retrieved successfully',
            'data' => CompanyResource::collection($companies->items()),
            'meta' => [
                'current_page' => $companies->currentPage(),
                'last_page' => $companies->lastPage(),
                'per_page' => $companies->perPage(),
                'total' => $companies->total(),
                'from' => $companies->firstItem(),
                'to' => $companies->lastItem(),
            ],
            'links' => [
                'first' => $companies->url(1),
                'last' => $companies->url($companies->lastPage()),
                'prev' => $companies->previousPageUrl(),
                'next' => $companies->nextPageUrl(),
            ],
        ]);
    }

    public function show(Company $company): JsonResponse
    {
        return $this->success(
            new CompanyResource($this->companyService->find($company)),
            'Company retrieved successfully'
        );
    }
}
