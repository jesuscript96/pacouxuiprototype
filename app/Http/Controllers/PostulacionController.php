<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Vacante;
use App\Services\PostulacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostulacionController extends Controller
{
    public function __construct(
        private PostulacionService $postulacionService,
    ) {}

    public function mostrar(Empresa $empresa, Vacante $vacante): View
    {
        abort_unless($vacante->empresa_id === $empresa->id, 404);
        abort_if($vacante->trashed(), 404);

        $vacante->load(['camposFormulario' => fn ($q) => $q->orderBy('orden')]);

        return view('postulacion.formulario', [
            'empresa' => $empresa,
            'vacante' => $vacante,
        ]);
    }

    public function enviar(Request $request, Empresa $empresa, Vacante $vacante): RedirectResponse
    {
        abort_unless($vacante->empresa_id === $empresa->id, 404);
        abort_if($vacante->trashed(), 404);

        $vacante->load('camposFormulario');

        $rules = $this->construirReglasValidacion($vacante);
        $validated = $request->validate($rules);

        $this->postulacionService->crearCandidato($vacante, [
            'campos' => $validated['campos'] ?? [],
            'archivos' => $request->file('archivos') ?? [],
        ]);

        return redirect()
            ->route('postulacion.confirmacion')
            ->with('success', true)
            ->with('vacante', $vacante->puesto)
            ->with('empresa', $empresa->nombre);
    }

    public function confirmacion(): View|RedirectResponse
    {
        if (! session('success')) {
            return redirect('/');
        }

        return view('postulacion.confirmacion', [
            'vacante' => session('vacante'),
            'empresa' => session('empresa'),
        ]);
    }

    /**
     * BL: Construye reglas de validación dinámicas según los campos configurados en la vacante.
     *
     * @return array<string, list<string>>
     */
    private function construirReglasValidacion(Vacante $vacante): array
    {
        $rules = [];

        foreach ($vacante->camposFormulario as $campo) {
            $fieldRules = [];
            $fieldRules[] = $campo->requerido ? 'required' : 'nullable';

            switch ($campo->tipo) {
                case 'file':
                    $fieldRules[] = 'file';
                    $fieldRules[] = 'max:10240';
                    if ($campo->tipos_archivo) {
                        $mimes = $this->convertirTiposArchivoAMimes($campo->tipos_archivo);
                        if ($mimes !== '') {
                            $fieldRules[] = "mimes:{$mimes}";
                        }
                    }
                    $rules["archivos.{$campo->nombre}"] = $fieldRules;

                    continue 2;
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'select':
                    if (! empty($campo->opciones)) {
                        $fieldRules[] = 'in:'.implode(',', $campo->opciones);
                    }
                    break;
                case 'text':
                case 'textarea':
                    $fieldRules[] = 'string';
                    if ($campo->longitud_minima) {
                        $fieldRules[] = 'min:'.$campo->longitud_minima;
                    }
                    if ($campo->longitud_maxima) {
                        $fieldRules[] = 'max:'.$campo->longitud_maxima;
                    }
                    break;
                case 'phone':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:20';
                    break;
            }

            $rules["campos.{$campo->nombre}"] = $fieldRules;
        }

        return $rules;
    }

    /**
     * Convierte string de MIME types (image/png,application/pdf) a extensiones para la regla mimes.
     */
    private function convertirTiposArchivoAMimes(string $tiposArchivo): string
    {
        $mimeToExt = [
            'image/png' => 'png',
            'image/jpeg' => 'jpg,jpeg',
            'image/jpg' => 'jpg',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        ];

        $extensiones = [];

        foreach (explode(',', $tiposArchivo) as $mime) {
            $mime = trim($mime);
            if (isset($mimeToExt[$mime])) {
                $extensiones[] = $mimeToExt[$mime];
            }
        }

        return implode(',', $extensiones);
    }
}
