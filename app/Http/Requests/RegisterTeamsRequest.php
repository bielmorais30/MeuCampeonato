<?php

namespace App\Http\Requests;

use App\Models\Championship;
use App\Models\Registration;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterTeamsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $championship = $this->route('championship');
        $championshipId = is_object($championship) ? $championship->id : $championship;

        return [
            'team_id' => [
                'required',
                'integer',
                'exists:teams,id',
                Rule::unique('registrations', 'team_id')->where(function ($query) use ($championshipId) {
                    return $query->where('championship_id', $championshipId);
                }),
            ],
        ];
    }

    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $championship = $this->route('championship');
            $championshipId = is_object($championship) ? $championship->id : $championship;
            $championship = Championship::find($championshipId);

            if ($championship && $championship->status != 'pending') {
                $validator->errors()->add('team_id', 'As inscrições para este campeonato estão fechadas.');
            }

            $registeredTeamsCount = Registration::where('championship_id', $championshipId)->count();

            if ($registeredTeamsCount >= 8) {
                $validator->errors()->add('team_id', 'Este campeonato já possui 8 times registrados.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'team_id.unique' => 'Este time já está registrado neste campeonato.',
            'team_id.required' => 'O time é obrigatório.',
            'team_id.exists' => 'O time informado não existe.',
        ];
    }
}
