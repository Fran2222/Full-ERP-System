<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CrmActivity;
use App\Models\CrmFollowUp;
use App\Models\CrmLead;
use App\Models\CrmPipelineStage;
use App\Models\ProjectMgmt\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CRMController extends Controller
{
    public function dashboard()
    {
        return view('modules.project-crm.dashboard');
    }

    public function pipeline()
    {
        $stages = CrmPipelineStage::with([
                'leads' => function ($query) {
                    $query->with('assignedUser')
                        ->latest();
                }
            ])
            ->where('status', 'active')
            ->orderBy('position')
            ->get();

        return view('modules.project-crm.pipeline', compact('stages'));
    }

    public function storeLead(Request $request)
    {
        $data = $request->validate([
            'stage_id' => ['required', 'exists:crm_pipeline_stages,id'],
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:100'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'expected_close_date' => ['nullable', 'date'],
            'next_follow_up_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ], [
            'stage_id.required' => 'Please select a pipeline stage.',
            'stage_id.exists' => 'The selected pipeline stage is invalid.',
            'company_name.required' => 'Please provide a company or lead name.',
            'priority.required' => 'Please select a priority.',
            'priority.in' => 'Please select a valid priority.',
            'email.email' => 'Please provide a valid email address.',
            'estimated_value.numeric' => 'Estimated value must be a valid number.',
            'estimated_value.min' => 'Estimated value must not be negative.',
            'expected_close_date.date' => 'Please provide a valid expected close date.',
            'next_follow_up_date.date' => 'Please provide a valid next follow-up date.',
        ]);

        $stage = CrmPipelineStage::where('status', 'active')
            ->where('id', $data['stage_id'])
            ->firstOrFail();

        $lead = CrmLead::create([
            'lead_code' => $this->generateLeadCode(),
            'stage_id' => $stage->id,
            'company_name' => $data['company_name'],
            'contact_person' => $data['contact_person'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'source' => $data['source'] ?? null,
            'priority' => $data['priority'] ?? 'medium',
            'assigned_to' => Auth::id(),
            'estimated_value' => $data['estimated_value'] ?? null,
            'expected_close_date' => $data['expected_close_date'] ?? null,
            'next_follow_up_date' => $data['next_follow_up_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        CrmActivity::create([
            'crm_lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => 'created',
            'description' => 'Lead created under ' . $stage->name . ' stage.',
            'old_stage_id' => null,
            'new_stage_id' => $stage->id,
        ]);

        return redirect()
            ->route('crm.pipeline')
            ->with('success', 'Lead created successfully.');
    }

    private function generateLeadCode(): string
    {
        $nextNumber = (CrmLead::withTrashed()->max('id') ?? 0) + 1;

        do {
            $code = 'CRM-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (CrmLead::withTrashed()->where('lead_code', $code)->exists());

        return $code;
    }

    public function duplicateStage(CrmPipelineStage $stage)
    {
        $baseName = $stage->name . ' Copy';
        $name = $baseName;
        $counter = 1;

        while (CrmPipelineStage::where('name', $name)->exists()) {
            $counter++;
            $name = $baseName . ' ' . $counter;
        }

        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $slugCounter = 1;

        while (CrmPipelineStage::where('slug', $slug)->exists()) {
            $slugCounter++;
            $slug = $baseSlug . '-' . $slugCounter;
        }

        CrmPipelineStage::create([
            'name' => $name,
            'slug' => $slug,
            'position' => (CrmPipelineStage::max('position') ?? 0) + 1,
            'color' => $stage->color,
            'is_default' => false,
            'is_locked' => false,
            'status' => 'active',
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('crm.pipeline')
            ->with('success', 'Pipeline stage duplicated successfully.');
    }

    public function renameStage(Request $request, CrmPipelineStage $stage)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ], [
            'name.required' => 'Please provide a stage name.',
            'name.max' => 'Stage name must not exceed 100 characters.',
        ]);

        $name = trim($validated['name']);
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (
            CrmPipelineStage::where('slug', $slug)
                ->where('id', '!=', $stage->id)
                ->exists()
        ) {
            $counter++;
            $slug = $baseSlug . '-' . $counter;
        }

        $stage->update([
            'name' => $name,
            'slug' => $slug,
        ]);

        return redirect()
            ->route('crm.pipeline')
            ->with('success', 'Pipeline stage renamed successfully.');
    }

    public function showLead(CrmLead $lead)
    {
        $lead->load([
            'stage',
            'client',
            'assignedUser',
            'creator',
            'updater',
            'followUps.assignedUser',
            'followUps.creator',
            'activities.user',
            'activities.oldStage',
            'activities.newStage',
        ]);

        return view('modules.project-crm.leads.show', compact('lead'));
    }

    public function editLead(CrmLead $lead)
    {
        $lead->load([
            'stage',
            'assignedUser',
        ]);

        $stages = CrmPipelineStage::where('status', 'active')
            ->orderBy('position')
            ->get();

        return view('modules.project-crm.leads.edit', compact('lead', 'stages'));
    }

    public function updateLead(Request $request, CrmLead $lead)
    {
        $validated = $request->validate([
            'stage_id' => ['required', 'exists:crm_pipeline_stages,id'],
            'company_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:100'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'expected_close_date' => ['nullable', 'date'],
            'next_follow_up_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ], [
            'stage_id.required' => 'Please select a pipeline stage.',
            'stage_id.exists' => 'The selected pipeline stage is invalid.',
            'company_name.required' => 'Please provide a company or lead name.',
            'priority.required' => 'Please select a priority.',
            'priority.in' => 'Please select a valid priority.',
            'email.email' => 'Please provide a valid email address.',
            'estimated_value.numeric' => 'Estimated value must be a valid number.',
            'estimated_value.min' => 'Estimated value must not be negative.',
            'expected_close_date.date' => 'Please provide a valid expected close date.',
            'next_follow_up_date.date' => 'Please provide a valid next follow-up date.',
        ]);

        $oldStageId = $lead->stage_id;

        $lead->update([
            'stage_id' => $validated['stage_id'],
            'company_name' => $validated['company_name'],
            'contact_person' => $validated['contact_person'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'source' => $validated['source'] ?? null,
            'priority' => $validated['priority'],
            'estimated_value' => $validated['estimated_value'] ?? null,
            'expected_close_date' => $validated['expected_close_date'] ?? null,
            'next_follow_up_date' => $validated['next_follow_up_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        CrmActivity::create([
            'crm_lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => 'updated',
            'description' => 'Lead details updated.',
            'old_stage_id' => $oldStageId,
            'new_stage_id' => $validated['stage_id'],
        ]);

        return redirect()
            ->route('crm.leads.show', $lead->id)
            ->with('success', 'Lead updated successfully.');
    }

    public function storeLeadFollowUp(Request $request, CrmLead $lead)
    {
        $validated = $request->validate([
            'follow_up_type' => ['required', 'string', 'max:100'],
            'scheduled_at' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ], [
            'follow_up_type.required' => 'Please select a follow-up type.',
            'scheduled_at.required' => 'Please select a follow-up schedule.',
            'scheduled_at.date' => 'Please provide a valid follow-up schedule.',
            'remarks.max' => 'Remarks must not exceed 5000 characters.',
        ]);

        $lead->followUps()->create([
            'follow_up_type' => $validated['follow_up_type'],
            'scheduled_at' => $validated['scheduled_at'],
            'remarks' => $validated['remarks'] ?? null,
            'status' => 'pending',
            'assigned_to' => Auth::id(),
            'created_by' => Auth::id(),
        ]);

        CrmActivity::create([
            'crm_lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => 'follow_up_added',
            'description' => 'Follow-up scheduled: ' . ucwords(str_replace('_', ' ', $validated['follow_up_type'])) . '.',
            'old_stage_id' => $lead->stage_id,
            'new_stage_id' => $lead->stage_id,
        ]);

        return redirect()
            ->route('crm.leads.show', $lead->id)
            ->with('success', 'Follow-up added successfully.');
    }
    
    public function archiveStage(CrmPipelineStage $stage)
    {
        if ($stage->is_locked) {
            return redirect()
                ->route('crm.pipeline')
                ->with('error', 'This default stage cannot be archived.');
        }

        if ($stage->leads()->exists()) {
            return redirect()
                ->route('crm.pipeline')
                ->with('error', 'Move or archive the leads inside this stage before archiving the stage.');
        }

        $stage->update([
            'status' => 'inactive',
        ]);

        return redirect()
            ->route('crm.pipeline')
            ->with('success', 'Pipeline stage archived successfully.');
    }

    public function updateLeadStage(Request $request, CrmLead $lead)
    {
        $validated = $request->validate([
            'stage_id' => ['required', 'exists:crm_pipeline_stages,id'],
        ]);

        $oldStageId = $lead->stage_id;
        $newStageId = $validated['stage_id'];

        if ((int) $oldStageId === (int) $newStageId) {
            return response()->json([
                'success' => true,
                'message' => 'Lead already belongs to this stage.',
            ]);
        }

        $oldStage = CrmPipelineStage::find($oldStageId);
        $newStage = CrmPipelineStage::find($newStageId);

        $lead->update([
            'stage_id' => $newStageId,
            'updated_by' => Auth::id(),
        ]);

        CrmActivity::create([
            'crm_lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => 'stage_changed',
            'description' => 'Moved lead from ' . ($oldStage->name ?? 'Unknown') . ' to ' . ($newStage->name ?? 'Unknown') . '.',
            'old_stage_id' => $oldStageId,
            'new_stage_id' => $newStageId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lead moved successfully.',
            'lead_id' => $lead->id,
            'old_stage_id' => $oldStageId,
            'new_stage_id' => $newStageId,
        ]);
    }

    public function updateFollowUpStatus(Request $request, CrmFollowUp $followUp)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,completed,missed,cancelled'],
        ]);

        $oldStatus = $followUp->status;
        $newStatus = $validated['status'];

        $followUp->update([
            'status' => $newStatus,
            'updated_by' => Auth::id(),
        ]);

        $lead = CrmLead::find($followUp->crm_lead_id);

        if ($lead) {
            CrmActivity::create([
                'crm_lead_id' => $lead->id,
                'user_id' => Auth::id(),
                'activity_type' => 'follow_up_' . $newStatus,
                'description' => 'Follow-up status changed from ' . ucfirst($oldStatus ?? 'pending') . ' to ' . ucfirst($newStatus) . '.',
                'old_stage_id' => $lead->stage_id,
                'new_stage_id' => $lead->stage_id,
            ]);

            return redirect()
                ->route('crm.leads.show', $lead->id)
                ->with('success', 'Follow-up status updated successfully.');
        }

        return redirect()
            ->route('crm.pipeline')
            ->with('success', 'Follow-up status updated successfully.');
    }

    public function reorderStages(Request $request)
    {
        $validated = $request->validate([
            'stages' => ['required', 'array'],
            'stages.*.id' => ['required', 'exists:crm_pipeline_stages,id'],
            'stages.*.position' => ['required', 'integer', 'min:1'],
        ]);

        foreach ($validated['stages'] as $stageData) {
            CrmPipelineStage::where('id', $stageData['id'])
                ->update([
                    'position' => $stageData['position'],
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pipeline stage order updated successfully.',
        ]);
    }

    public function updateStageColor(Request $request, CrmPipelineStage $stage)
    {
        $validated = $request->validate([
            'color' => ['required', 'in:primary,info,success,warning,danger,secondary,dark'],
        ]);

        $stage->update([
            'color' => $validated['color'],
        ]);

        return redirect()
            ->route('crm.pipeline')
            ->with('success', 'Stage color updated successfully.');
    }


    public function convertLeadToClient(CrmLead $lead)
    {
        if ($lead->client_id) {
            return redirect()
                ->route('crm.leads.show', $lead->id)
                ->with('warning', 'This lead is already converted to a client.');
        }

        $client = Client::whereRaw('LOWER(name) = ?', [strtolower($lead->company_name)])
            ->first();

        if (! $client) {
            $client = Client::create([
                'code' => $this->generateClientCode(),
                'name' => $lead->company_name,
                'contact_person' => $lead->contact_person,
                'contact_number' => $lead->phone,
                'email' => $lead->email,
                'address' => $lead->address,
                'remarks' => trim(
                    'Converted from CRM Lead: ' . $lead->lead_code .
                    ($lead->notes ? "\nNotes: " . $lead->notes : '')
                ),
                'status' => 'active',
            ]);
        } else {
            $client->update([
                'contact_person' => $client->contact_person ?: $lead->contact_person,
                'contact_number' => $client->contact_number ?: $lead->phone,
                'email' => $client->email ?: $lead->email,
                'address' => $client->address ?: $lead->address,
            ]);
        }

        $lead->update([
            'client_id' => $client->id,
            'updated_by' => Auth::id(),
        ]);

        CrmActivity::create([
            'crm_lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => 'converted_to_client',
            'description' => 'Lead converted to client: ' . $client->code . ' - ' . $client->name . '.',
            'old_stage_id' => $lead->stage_id,
            'new_stage_id' => $lead->stage_id,
        ]);

        return redirect()
            ->route('crm.leads.show', $lead->id)
            ->with('success', 'Lead converted to client successfully.');
    }

    public function createProjectFromLead(CrmLead $lead)
    {
        $client = Client::whereRaw('LOWER(name) = ?', [strtolower($lead->company_name)])
            ->first();

        if (! $client) {
            $client = Client::create([
                'code' => $this->generateClientCode(),
                'name' => $lead->company_name,
                'contact_person' => $lead->contact_person,
                'contact_number' => $lead->phone,
                'email' => $lead->email,
                'address' => $lead->address,
                'remarks' => trim(
                    'Converted from CRM Lead: ' . $lead->lead_code .
                    ($lead->notes ? "\nNotes: " . $lead->notes : '')
                ),
                'status' => 'active',
            ]);

            CrmActivity::create([
                'crm_lead_id' => $lead->id,
                'user_id' => Auth::id(),
                'activity_type' => 'converted_to_client',
                'description' => 'Client auto-created for project creation: ' . $client->code . ' - ' . $client->name . '.',
                'old_stage_id' => $lead->stage_id,
                'new_stage_id' => $lead->stage_id,
            ]);
        }
        CrmActivity::create([
            'crm_lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => 'project_creation_started',
            'description' => 'Project creation started from this CRM lead.',
            'old_stage_id' => $lead->stage_id,
            'new_stage_id' => $lead->stage_id,
        ]);

        return redirect()
            ->route('projects.create', [
                'client_id' => $client->id,
                'crm_lead_id' => $lead->id,
            ])
            ->with('success', 'Client is ready. Please complete the project details.');
    }

    public function archiveLead(CrmLead $lead)
    {
        CrmActivity::create([
            'crm_lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'activity_type' => 'archived',
            'description' => 'Lead archived.',
            'old_stage_id' => $lead->stage_id,
            'new_stage_id' => $lead->stage_id,
        ]);

        $lead->delete();

        return redirect()
            ->route('crm.pipeline')
            ->with('success', 'Lead archived successfully.');
    }

    private function generateClientCode(): string
    {
        $lastClient = Client::where('code', 'like', 'CL-%')
            ->orderByDesc('id')
            ->first();

        if ($lastClient && preg_match('/^CL-(\d+)$/', $lastClient->code, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $lastNumber = Client::where('code', 'like', 'CL-%')
                ->get()
                ->map(function ($client) {
                    if (preg_match('/^CL-(\d+)$/', $client->code, $matches)) {
                        return (int) $matches[1];
                    }

                    return 0;
                })
                ->max();

            $nextNumber = ((int) $lastNumber) + 1;
        }

        return 'CL-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

}
