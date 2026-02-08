<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Ticket title is required',
            'title.min' => 'Ticket title must be at least 5 characters',
            'title.max' => 'Ticket title cannot exceed 255 characters',
            'description.required' => 'Ticket description is required',
            'description.min' => 'Ticket description must be at least 10 characters',
            'description.max' => 'Ticket description cannot exceed 5000 characters',
        ];
    }
}
