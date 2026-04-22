<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ColaboradorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para importación masiva (mismo conjunto que el form).
     *
     * @return array<string, mixed>
     */
    public static function rulesForImport(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'required_without:telefono_movil', Rule::email()->withNativeValidation(true), 'max:255'],
            'telefono_movil' => ['nullable', 'required_without:email', 'string', 'size:10'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'genero' => ['nullable', 'string', 'in:M,F,OTRO'],
            'curp' => ['nullable', 'string', 'size:18', 'regex:/^[A-Za-z0-9]{18}$/'],
            'rfc' => ['nullable', 'string', 'max:13'],
            'nss' => ['nullable', 'string', 'size:11'],
            'fecha_ingreso' => ['required', 'date'],
            'periodicidad_pago' => ['required', 'string', 'in:SEMANAL,CATORCENAL,QUINCENAL,MENSUAL'],
            'salario_bruto' => ['nullable', 'numeric', 'min:0'],
            'salario_neto' => ['nullable', 'numeric', 'min:0'],
            'monto_maximo' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'required_without:telefono_movil', Rule::email()->withNativeValidation(true), 'max:255'],
            'telefono_movil' => ['nullable', 'required_without:email', 'string', 'size:10'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'genero' => ['nullable', 'string', 'in:M,F,OTRO'],
            'curp' => ['nullable', 'string', 'size:18', 'regex:/^[A-Za-z0-9]{18}$/'],
            'rfc' => ['nullable', 'string', 'max:13'],
            'nss' => ['nullable', 'string', 'size:11'],
            'fecha_ingreso' => ['required', 'date'],
            'periodicidad_pago' => ['required', 'string', 'in:SEMANAL,CATORCENAL,QUINCENAL,MENSUAL'],
            'salario_bruto' => ['nullable', 'numeric', 'min:0'],
            'salario_neto' => ['nullable', 'numeric', 'min:0'],
            'monto_maximo' => ['nullable', 'numeric', 'min:0'],
            'fecha_registro_imss' => ['nullable', 'date'],
            'nombre_empresa_pago' => ['nullable', 'string', 'max:255'],
            'beneficiarios' => ['nullable', 'array'],
            'beneficiarios.*.nombre_completo' => ['required', 'string'],
            'beneficiarios.*.parentesco' => ['required', 'string'],
            'beneficiarios.*.porcentaje' => ['required', 'numeric', 'min:0', 'max:100'],
            'cuenta_nomina' => ['nullable', 'array'],
            'cuenta_nomina.banco_id' => ['nullable', 'integer', 'exists:bancos,id'],
            'cuenta_nomina.numero_cuenta' => ['nullable', 'string', 'max:50'],
            'cuenta_nomina.tipo_cuenta' => ['nullable', 'string', 'in:CLABE,TARJETA,CUENTA'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $beneficiarios = $this->input('beneficiarios', []);
            if ($beneficiarios !== [] && is_array($beneficiarios)) {
                $suma = 0.0;
                foreach ($beneficiarios as $b) {
                    $suma += (float) ($b['porcentaje'] ?? 0);
                }
                if (abs($suma - 100.0) > 0.01) {
                    $validator->errors()->add(
                        'beneficiarios',
                        'La suma de porcentajes de beneficiarios debe ser 100.'
                    );
                }
            }
        });
    }
}
