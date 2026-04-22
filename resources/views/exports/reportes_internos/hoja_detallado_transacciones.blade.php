<table>
    <thead>
        <tr>
            <th>Empresa</th>
            <th>ID de cuenta por cobrar</th>
            <th>Estado de la cuenta por cobrar</th>
            <th>Estado de la transacción</th>
            <th>Tipo de transacción</th>
            <th>Día de pago</th>
            <th>Subtotal</th>
            <th>Comisión</th>
            <th>Costo producto</th>
            <th>Centro de costos dispersión</th>
            <th>Centro de costos cobro</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($filas as $fila)
            <tr>
                <td>{{ $fila['empresa'] }}</td>
                <td>{{ $fila['cuenta_por_cobrar_id'] }}</td>
                <td>{{ $fila['estado_cuenta_por_cobrar'] }}</td>
                <td>{{ $fila['estado_transaccion'] }}</td>
                <td>{{ $fila['tipo_transaccion'] }}</td>
                <td>{{ $fila['fecha_confirmacion_pago'] }}</td>
                <td>{{ $fila['subtotal'] }}</td>
                <td>{{ $fila['comision'] }}</td>
                <td>{{ $fila['costo_producto'] }}</td>
                <td>{{ $fila['centro_costo_dispersion'] }}</td>
                <td>{{ $fila['centro_costo_cobro'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
