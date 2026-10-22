<?php

namespace App\Http\Controllers;

use App\Domain\Projects\Services\GenerateProjectCode;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function __construct(
        private readonly GenerateProjectCode $generateCode,
    ) {}

    public function index(): Response
    {
        $projects = Project::with('client')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->through(fn (Project $project) => [
                'id' => $project->id,
                'code' => $project->code,
                'name' => $project->name,
                'client_name' => $project->client->name,
                'status' => $project->status->value,
                'created_at' => $project->created_at->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
        ]);
    }

    public function create(): Response
    {
        $clients = Client::orderBy('name')->get()->map(fn (Client $client) => [
            'id' => $client->id,
            'name' => $client->name,
        ]);

        $users = User::where('is_active', true)->orderBy('name')->get()->map(fn (User $user) => [
            'id' => $user->id,
            'name' => $user->name,
        ]);

        return Inertia::render('Projects/Create', [
            'clients' => $clients,
            'users' => $users,
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['code'] = ($this->generateCode)();
        $data['created_by'] = $request->user()->id;

        Project::create($data);

        return to_route('projects.index')->with('success', 'Proyecto creado exitosamente.');
    }

    public function show(Project $project): Response
    {
        $clients = Client::orderBy('name')->get()->map(fn (Client $client) => [
            'id' => $client->id,
            'name' => $client->name,
        ]);

        $users = User::where('is_active', true)->orderBy('name')->get()->map(fn (User $user) => [
            'id' => $user->id,
            'name' => $user->name,
        ]);

        return Inertia::render('Projects/Show', [
            'project' => [
                'id' => $project->id,
                'code' => $project->code,
                'client_id' => $project->client_id,
                'name' => $project->name,
                'status' => $project->status->value,
                'installation_type' => $project->installation_type->value,
                'property_type' => $project->property_type,
                'street' => $project->street,
                'exterior_number' => $project->exterior_number,
                'neighborhood' => $project->neighborhood,
                'municipality' => $project->municipality,
                'state' => $project->state,
                'postal_code' => $project->postal_code,
                'latitude' => $project->latitude,
                'longitude' => $project->longitude,
                'objectives' => $project->objectives,
                'property_data' => $project->property_data,
                'notes' => $project->notes,
                'assigned_user_id' => $project->assigned_user_id,
                'created_at' => $project->created_at->format('Y-m-d H:i'),
                'isMissingCoordinates' => $project->isMissingCoordinates(),
            ],
            'clients' => $clients,
            'users' => $users,
        ]);
    }

    public function edit(Project $project): Response
    {
        $clients = Client::orderBy('name')->get()->map(fn (Client $client) => [
            'id' => $client->id,
            'name' => $client->name,
        ]);

        $users = User::where('is_active', true)->orderBy('name')->get()->map(fn (User $user) => [
            'id' => $user->id,
            'name' => $user->name,
        ]);

        return Inertia::render('Projects/Edit', [
            'project' => [
                'id' => $project->id,
                'code' => $project->code,
                'client_id' => $project->client_id,
                'name' => $project->name,
                'status' => $project->status->value,
                'installation_type' => $project->installation_type->value,
                'property_type' => $project->property_type,
                'street' => $project->street,
                'exterior_number' => $project->exterior_number,
                'neighborhood' => $project->neighborhood,
                'municipality' => $project->municipality,
                'state' => $project->state,
                'postal_code' => $project->postal_code,
                'latitude' => $project->latitude,
                'longitude' => $project->longitude,
                'objectives' => $project->objectives,
                'property_data' => $project->property_data,
                'notes' => $project->notes,
                'assigned_user_id' => $project->assigned_user_id,
                'isMissingCoordinates' => $project->isMissingCoordinates(),
            ],
            'clients' => $clients,
            'users' => $users,
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return to_route('projects.show', $project)->with('success', 'Proyecto actualizado exitosamente.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return to_route('projects.index')->with('success', 'Proyecto eliminado exitosamente.');
    }
}
