<table>
    <thead>
        <tr>
            <th>ID de cuenta por cobrar</th>
            <th>ID del colaborador</th>
            <th>Empresa</th>
            <th>Ubicación</th>
            <th>Día de pago</th>
            <th>Tipo de confirmación de pago</th>
            <th>Penalizaciones</th>
            <th>Subtotal</th>
            <th>Comisión</th>
            <th>Costo producto</th>
            <th>Total de cuenta por cobrar</th>
            <th>Comisiones bancarias</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($filas as $fila)
            <tr>
                <td>{{ $fila['cuenta_por_cobrar_id'] }}</td>
                <td>{{ $fila['user_id'] }}</td>
                <td>{{ $fila['empresa'] }}</td>
                <td>{{ $fila['ubicacion'] }}</td>
                <td>{{ $fila['fecha_confirmacion_pago'] }}</td>
                <td>{{ $fila['tipo_confirmacion'] }}</td>
                <td>{{ $fila['penalizaciones'] }}</td>
                <td>{{ $fila['subtotal'] }}</td>
                <td>{{ $fila['comisiones'] }}</td>
                <td>{{ $fila['costo_producto'] }}</td>
                <td>{{ $fila['total_cuenta'] }}</td>
                <td>{{ $fila['comisiones_bancarias'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
