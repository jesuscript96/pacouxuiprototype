<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Producto;
use App\Models\User;

class AsignacionProductosService
{
    /**
     * Asigna productos de la empresa al colaborador (User) según filtros y deudas.
     * Cumple filtros y sin deudas → ACTIVO.
     * Cumple filtros con deudas → INACTIVO, razon = DEUDA_PENDIENTE.
     * No cumple filtros → no se asigna.
     */
    public function asignarProductosEmpresa(User $colaborador, Empresa $empresa): void
    {
        $productos = $empresa->productos()->get();
        $tieneDeudas = $this->colaboradorTieneDeudas($colaborador);

        foreach ($productos as $producto) {
            if (! $this->colaboradorCumpleFiltrosProducto($colaborador, $producto)) {
                continue;
            }

            $pivotBase = [
                'tipo_cambio' => null,
            ];
            if ($colaborador->colaborador_id !== null) {
                $pivotBase['colaborador_id'] = $colaborador->colaborador_id;
            }

            if ($tieneDeudas) {
                $colaborador->productos()->syncWithoutDetaching([
                    $producto->id => array_merge($pivotBase, [
                        'estado' => 'INACTIVO',
                        'razon' => 'DEUDA_PENDIENTE',
                    ]),
                ]);
            } else {
                $colaborador->productos()->syncWithoutDetaching([
                    $producto->id => array_merge($pivotBase, [
                        'estado' => 'ACTIVO',
                        'razon' => null,
                    ]),
                ]);
            }
        }
    }

    /**
     * Quita todos los productos del colaborador y vuelve a asignar según empresa.
     */
    public function reevaluarProductos(User $colaborador): void
    {
        $colaborador->productos()->detach();
        $empresa = $colaborador->empresa;
        if ($empresa) {
            $this->asignarProductosEmpresa($colaborador, $empresa);
        }
    }

    /**
     * Evalúa si el colaborador cumple los filtros del producto.
     * Cuando existan filtros configurados por producto (ej. tabla producto_filtros o JSON en empresas_productos),
     * validar: region_id, ubicacion_id, area_id, departamento_id, puesto_id, genero, edad_minima, edad_maxima, meses_desde_ingreso.
     */
    protected function colaboradorCumpleFiltrosProducto(User $colaborador, Producto $producto): bool
    {
        // Sin filtros configurados en el proyecto: todos los productos de la empresa aplican.
        return true;
    }

    /**
     * Verifica si el colaborador tiene deudas (cuentas por cobrar PENDIENTE/CONTRACARGO/INCOBRABLE).
     * Cuando exista tabla cuentas_por_cobrar para colaboradores, consultar estado.
     */
    protected function colaboradorTieneDeudas(User $colaborador): bool
    {
        // cuentas_por_cobrar_empleado existe para empleados; no hay equivalente para colaboradores aún.
        return false;
    }
}
