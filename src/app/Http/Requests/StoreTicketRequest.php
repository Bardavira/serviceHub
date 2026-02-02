<?php

namespace App\Http\Requests;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        $auth = $this->user();

        if (!$auth) {
            return false;
        }

        $projectId = (int) $this->input('project_id');
        $assigneeId = (int) $this->input('user_id');

        $projectOk = Project::query()
            ->whereKey($projectId)
            ->where('company_id', $auth->company_id)
            ->exists();

        $assigneeOk = User::query()
            ->whereKey($assigneeId)
            ->where('company_id', $auth->company_id)
            ->exists();

        return $projectOk && $assigneeOk;
    }

    protected function failedAuthorization()
    {
        abort(403, 'Project and assignee must belong to your company.');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:2048', 'mimes:txt,json'],
        ];
    }
}
