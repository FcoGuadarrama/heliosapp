<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(): Response
    {
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
            ->through(fn (Client $client) => [
                'id' => $client->id,
                'type' => $client->type->value,
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
        return Inertia::render('Clients/Create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        Client::create($data);

        return to_route('clients.index')->with('success', 'Cliente creado exitosamente.');
    }

    public function show(Client $client): Response
    {
        $projects = $client->projects()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($project) => [
                'id' => $project->id,
                'code' => $project->code,
                'name' => $project->name,
                'status' => $project->status->value,
                'created_at' => $client->created_at->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Clients/Show', [
            'client' => [
                'id' => $client->id,
                'type' => $client->type->value,
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
        ]);
    }

    public function edit(Client $client): Response
    {
        return Inertia::render('Clients/Edit', [
            'client' => [
                'id' => $client->id,
                'type' => $client->type->value,
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
        $client->update($request->validated());

        return to_route('clients.index')->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return to_route('clients.index')->with('success', 'Cliente eliminado exitosamente.');
    }
}
