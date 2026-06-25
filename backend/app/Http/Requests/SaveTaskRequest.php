<?php

namespace App\Http\Requests;

use App\TaskOptions\TaskPriority;
use App\TaskOptions\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', Rule::enum(TaskStatus::class)],
            'priority' => ['sometimes', 'required', Rule::enum(TaskPriority::class)],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }
}