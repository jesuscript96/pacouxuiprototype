<?php

declare(strict_types=1);

/**
 * Catálogo de informes Tableau incrustados (panel Cliente con tenant y panel Admin sin tenant).
 *
 * Para añadir un informe: copiar una entrada, cambiar la clave (slug), label, icon,
 * navigation_sort y rutas de vista. Opcional: embed_path_por_empresa_id.
 *
 * @var array<string, array{
 *     label: string,
 *     navigation_icon?: string|null,
 *     navigation_sort: int,
 *     embed_path: string,
 *     embed_path_por_empresa_id?: array<int, string>,
 * }>
 */
return [

    'rotacion_personal' => [
        'label' => 'Rotación de personal',
        'navigation_icon' => 'heroicon-o-arrow-path-rounded-square',
        'navigation_sort' => 1,
        'embed_path' => 'views/Rotacion/RotacindePersonal',
    ],

    'demograficos' => [
        'label' => 'Demográficos',
        'navigation_icon' => 'heroicon-o-chart-pie',
        'navigation_sort' => 2,
        'embed_path' => 'views/DEMOGRFICOS/DEMOGRFICOS',
    ],

    'satisfaccion_enps' => [
        'label' => 'Satisfacción (eNPS)',
        'navigation_icon' => 'heroicon-o-face-smile',
        'navigation_sort' => 3,
        'embed_path' => 'views/SatisfaccionColaboradoresSentiment/SentimentColaboradores',
    ],

    'encuestas' => [
        'label' => 'Encuestas',
        'navigation_icon' => 'heroicon-o-clipboard-document-list',
        'navigation_sort' => 4,
        'embed_path' => 'views/Encuestas/SegmentacinySentimientodeEncuestas',
    ],

    'encuestas_plan_accion' => [
        'label' => 'Encuestas - Plan de Acción',
        'navigation_icon' => 'heroicon-o-clipboard-document-check',
        'navigation_sort' => 5,
        'embed_path' => 'views/AnlisisdeEncuestasconIA/AnlisisdeEncuestasconIA',
    ],

    'resultados_nom_035' => [
        'label' => 'Resultados NOM-035',
        'navigation_icon' => 'heroicon-o-document-chart-bar',
        'navigation_sort' => 6,
        'embed_path' => 'views/REPORTENOM035/GUAINOM-035',
    ],

    'voz_colaborador' => [
        'label' => 'Voz del Colaborador',
        'navigation_icon' => 'heroicon-o-chat-bubble-left-right',
        'navigation_sort' => 7,
        'embed_path' => 'views/VozdelEmpleado1/SentimientoyVozdelColaborador',
    ],

    'reconocimientos' => [
        'label' => 'Reconocimientos',
        'navigation_icon' => 'heroicon-o-trophy',
        'navigation_sort' => 8,
        'embed_path' => 'views/Reconocimientos/Reconocimientos',
    ],

    'mensajes' => [
        'label' => 'Mensajes',
        'navigation_icon' => 'heroicon-o-envelope',
        'navigation_sort' => 9,
        'embed_path' => 'views/Mensajes/Mensajes',
    ],

    'salud_mental' => [
        'label' => 'Salud Mental',
        'navigation_icon' => 'heroicon-o-heart',
        'navigation_sort' => 10,
        'embed_path' => 'views/BienestardeColaboradores/BienestardeColaboradores',
    ],

    'transacciones' => [
        'label' => 'Transacciones',
        'navigation_icon' => 'heroicon-o-banknotes',
        'navigation_sort' => 11,
        'embed_path' => 'views/Transacciones/Transaccionalidad',
    ],

    'descuentos' => [
        'label' => 'Descuentos',
        'navigation_icon' => 'heroicon-o-receipt-percent',
        'navigation_sort' => 12,
        'embed_path' => 'views/Descuentos_17616932198930/DESCUENTOS',
    ],

    'reclutamiento' => [
        'label' => 'Reclutamiento',
        'navigation_icon' => 'heroicon-o-user-plus',
        'navigation_sort' => 13,
        'embed_path' => 'views/Reclutamiento/Reclutamiento',
    ],

];
