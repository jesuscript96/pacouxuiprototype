<?php

return [

    'single' => [

        'label' => 'Borrar',

        'modal' => [

            // 'heading' => 'Borrarssss :label',
            'heading' => '¿Deseas borrar este registro?',

            'actions' => [

                'delete' => [
                    'label' => 'Borrar',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'Borrado',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'Borrar seleccionados',

        'modal' => [

            'heading' => 'B¿Deseas borrar los registros seleccionados?',
            // 'heading' => 'Borrar :label seleccionados',

            'actions' => [

                'delete' => [
                    'label' => 'Borrar',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'Borrados',
            ],

            'deleted_partial' => [
                'title' => 'Borrados :count de :total',
                'missing_authorization_failure_message' => 'Usted no tiene permiso para eliminar :count.',
                'missing_processing_failure_message' => ':count no se pudieron eliminar.',
            ],

            'deleted_none' => [
                'title' => 'No se pudo eliminar',
                'missing_authorization_failure_message' => 'Usted no tiene permiso para eliminar :count.',
                'missing_processing_failure_message' => ':count no se pudieron eliminar.',
            ],

        ],

    ],

];
