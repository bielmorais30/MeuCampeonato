<?php

namespace App\Http\Requests;

use App\Models\Registration;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterMultipleTeamsRequest extends FormRequest
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
            'team_ids' => ['required', 'array', 'min:1'],
            'team_ids.*' => [
                'required',
                'integer',
                'exists:teams,id',
                Rule::unique('registrations', 'team_id')->where(function ($query) use ($championshipId) {
                    return $query->where('championship_id', $championshipId);
                }),
            ],
        ];
    }

    protected function withValidator($validator) // Segunda validação para verificar o status do campeonato e o número total de times registrados
    {
        $validator->after(function ($validator) {
            $championship = $this->route('championship');
            $championshipId = is_object($championship) ? $championship->id : $championship;
            $championship = \App\Models\Championship::find($championshipId);

            if ($championship && $championship->status != 'pending') {
                $validator->errors()->add('team_ids', 'As inscrições para este campeonato estão fechadas.');
            }

            $registeredTeamsCount = Registration::where('championship_id', $championshipId)->count();
            $teamIds = $this->team_ids ?? [];
            $newTeamsCount = is_array($teamIds) ? count($teamIds) : 0;
            $totalTeams = $registeredTeamsCount + $newTeamsCount;

            if ($totalTeams > 8 && $newTeamsCount > 0) {
                $validator->errors()->add('team_ids', 'Não é possível registrar ' . $newTeamsCount . ' times. Máximo de 8 times por campeonato. Já existem ' . $registeredTeamsCount . ' registrados.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'team_ids.required' => 'Nenhum time foi selecionado.',
            'team_ids.array' => 'Times deve ser um array.',
            'team_ids.min' => 'Selecione pelo menos um time.',
            'team_ids.*.required' => 'Alguns times não foram informados.',
            'team_ids.*.integer' => 'Cada time deve ser um número válido.',
            'team_ids.*.exists' => 'Um ou mais times selecionados não existem.',
            'team_ids.*.unique' => 'Um ou mais times já estão registrados neste campeonato.',
        ];
    }
}
