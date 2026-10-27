<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ReceiptAnalysis;
use App\Infrastructure\Hsp\StateGrid;
use App\Models\Client;
use App\Models\ReceiptIntake;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    use AuthorizesRequests;

    public function index(): Response
    {
        $this->authorize('viewAny', Client::class);

        $search = request('search', '');
        $page = request('page', 1);

        $query = Client::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Client $client) => [
                'id' => $client->id,
                'type' => $client->type?->value,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'municipality' => $client->municipality,
            ]);

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'search' => $search,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Client::class);

        return Inertia::render('Clients/Create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $this->authorize('create', Client::class);

        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        Client::create($data);

        return to_route('clients.index')->with('success', 'Cliente creado exitosamente.');
    }

    public function show(Client $client, ReceiptAnalysis $analysis, StateGrid $states): Response
    {
        $this->authorize('view', $client);

        $possibleDuplicates = [];
        if ($client->email || $client->phone) {
            $possibleDuplicates = Client::query()
                ->where('id', '<>', $client->id)
                ->where(function ($query) use ($client): void {
                    if ($client->email) {
                        $query->orWhere('email', $client->email);
                    }
                    if ($client->phone) {
                        $query->orWhere('phone', $client->phone);
                    }
                })
                ->orderBy('id')->limit(5)->get(['id', 'name'])
                ->filter(fn (Client $candidate) => request()->user()->can('view', $candidate))
                ->map(fn (Client $candidate) => ['id' => $candidate->id, 'name' => $candidate->name])
                ->values();
        }

        $projects = request()->user()->can('projects.view') ? $client->projects()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($project) => [
                'id' => $project->id,
                'code' => $project->code,
                'name' => $project->name,
                'status' => $project->status->value,
                'created_at' => $project->created_at->format('Y-m-d H:i'),
            ]) : [];

        return Inertia::render('Clients/Show', [
            'client' => [
                'id' => $client->id,
                'type' => $client->type?->value,
                'name' => $client->name,
                'contact_name' => $client->contact_name,
                'email' => $client->email,
                'phone' => $client->phone,
                'rfc' => $client->rfc,
                'street' => $client->street,
                'exterior_number' => $client->exterior_number,
                'interior_number' => $client->interior_number,
                'neighborhood' => $client->neighborhood,
                'municipality' => $client->municipality,
                'state' => $client->state,
                'postal_code' => $client->postal_code,
                'notes' => $client->notes,
            ],
            'projects' => $projects,
            'possibleDuplicates' => $possibleDuplicates,
            'stateOptions' => array_map(fn ($state): array => ['code' => $state->code, 'name' => $state->name], $states->states),
            'intakes' => request()->user()->can('receipts.view')
                ? $client->intakes()->latest()->get()->map(fn (ReceiptIntake $intake) => [
                    'id' => $intake->id,
                    'status' => $intake->status,
                    'mime_type' => $intake->mime_type,
                    'created_at' => $intake->created_at->format('Y-m-d H:i'),
                    'download_url' => route('intakes.file', $intake),
                    'reader_identifier' => $intake->reader_identifier,
                    'fixture_identifier' => $intake->fixture_identifier,
                    'validation_result' => $intake->fixture_identifier === null ? null : $intake->validation_result,
                    'analysis' => $intake->fixture_identifier === null ? $analysis->forAdvisor($intake, request()->user()) : null,
                ]) : [],
        ]);
    }

    public function edit(Client $client): Response
    {
        $this->authorize('update', $client);

        return Inertia::render('Clients/Edit', [
            'client' => [
                'id' => $client->id,
                'type' => $client->type?->value,
                'name' => $client->name,
                'contact_name' => $client->contact_name,
                'email' => $client->email,
                'phone' => $client->phone,
                'rfc' => $client->rfc,
                'street' => $client->street,
                'exterior_number' => $client->exterior_number,
                'interior_number' => $client->interior_number,
                'neighborhood' => $client->neighborhood,
                'municipality' => $client->municipality,
                'state' => $client->state,
                'postal_code' => $client->postal_code,
                'notes' => $client->notes,
            ],
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        $client->update($request->validated());

        return to_route('clients.index')->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        $client->delete();

        return to_route('clients.index')->with('success', 'Cliente eliminado exitosamente.');
    }
}
