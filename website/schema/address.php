<?php //-->
return [
  'singular' => 'Address',
  'plural' => 'Addresses',
  'name' => 'address',
  'group' => 'Users',
  'icon' => 'fas fa-map-marker-alt',
  'detail' => 'Manages Addresses',
  'fields' => [
    [
      'label' => 'Label',
      'name' => 'label',
      'field' => [
        'type' => 'text',
        'attributes' => [
          'placeholder' => 'eg. My Home'
        ]
      ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Label is required'
        ]
      ],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => null,
      'searchable' => 1,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Street 1',
      'name' => 'street_1',
      'field' => [
        'type' => 'text',
        'attributes' => [
          'placeholder' => '123 Sesame Street'
        ]
      ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Street 1 is required'
        ]
      ],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => null,
      'searchable' => 1,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Street 2',
      'name' => 'street_2',
      'field' => [
        'type' => 'text',
        'attributes' => [
          'placeholder' => 'eg. Unit 100, Building B'
        ]
      ],
      'validation' => [],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Neighborhood',
      'name' => 'neighborhood',
      'field' => [
        'type' => 'text',
        'attributes' => [
          'placeholder' => 'eg. Skyler Plains'
        ]
      ],
      'validation' => [],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => null,
      'searchable' => 1,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'City',
      'name' => 'city',
      'field' => [
        'type' => 'text',
        'attributes' => [
          'placeholder' => 'eg. White Plains'
        ]
      ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'City is required'
        ]
      ],
      'list' => ['format' => 'capital'],
      'detail' => ['format' => 'capital'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 0
    ],
    [
      'label' => 'State',
      'name' => 'state',
      'field' => [
        'type' => 'text',
        'attributes' => [
          'placeholder' => 'eg. New York'
        ]
      ],
      'validation' => [],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 0
    ],
    [
      'label' => 'Region',
      'name' => 'region',
      'field' => [
        'type' => 'text',
        'attributes' => [
          'placeholder' => 'eg. North East'
        ]
      ],
      'validation' => [],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 0
    ],
    [
      'label' => 'Country',
      'name' => 'country',
      'field' => [
        'type' => 'select',
        'attributes' => [
          'data-do' => 'country-dropdown'
        ]
      ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Country is required'
        ],
        [
          'method' => 'regexp',
          'parameters' => ['#^[A-Z]{2}$#'],
          'message' => 'Should be a valid country code format'
        ]
      ],
      'list' => ['format' => 'upper'],
      'detail' => ['format' => 'upper'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 0
    ],
    [
      'label' => 'Postal Code',
      'name' => 'postal_code',
      'field' => [
        'type' => 'text',
        'attributes' => [
          'placeholder' => 'eg. 12345'
        ]
      ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Postal code is required'
        ]
      ],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 0
    ],
    [
      'label' => 'Landmarks',
      'name' => 'landmarks',
      'field' => [
        'type' => 'text',
        'attributes' => [
          'placeholder' => 'eg. Near McDonalds'
        ]
      ],
      'validation' => [],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Contact Name',
      'name' => 'contact_name',
      'field' => [
        'type' => 'text',
        'attributes' => [
          'placeholder' => 'eg. John Doe'
        ]
      ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Contact name is required'
        ]
      ],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => null,
      'searchable' => 1,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Contact Email',
      'name' => 'contact_email',
      'field' => [
        'type' => 'email',
        'attributes' => [
          'placeholder' => 'eg. John Doe'
        ]
      ],
      'validation' => [
        [
          'method' => 'email',
          'message' => 'Should be a valid email format'
        ]
      ],
      'list' => [
        'format' => 'email',
        'parameters' => ['{{address_contact_email}}']
      ],
      'detail' => [
        'format' => 'email',
        'parameters' => ['{{address_contact_email}}']
      ],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Contact Phone',
      'name' => 'contact_phone',
      'field' => [
        'type' => 'text',
        'attributes' => [
          'placeholder' => 'eg. 555-2424'
        ]
      ],
      'validation' => [],
      'list' => [
        'format' => 'phone',
        'parameters' => ['{{address_contact_phone}}']
      ],
      'detail' => [
        'format' => 'phone',
        'parameters' => ['{{address_contact_phone}}']
      ],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Latitude',
      'name' => 'latitude',
      'field' => [
        'type' => 'float',
        'attributes' => [
          'min' => '-90',
          'max' => '90',
          'step' => '0.00000001'
        ]
      ],
      'validation' => [
        [
          'method' => 'number',
          'message' => 'Should be a valid number'
        ],
        [
          'method' => 'lte',
          'parameters' => ['90'],
          'message' => 'Should be less than 90'
        ],
        [
          'method' => 'gte',
          'parameters' => ['-90'],
          'message' => 'Should be greater than -90'
        ]
      ],
      'list' => [
        'format' => 'none'
      ],
      'detail' => [
        'format' => 'none'
      ],
      'default' => '0.00000000',
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Longitude',
      'name' => 'longitude',
      'field' => [
        'type' => 'float',
        'attributes' => [
          'min' => '-180',
          'max' => '180',
          'step' => '0.00000001'
        ]
      ],
      'validation' => [
        [
          'method' => 'number',
          'message' => 'Should be a valid number'
        ],
        [
          'method' => 'lte',
          'parameters' => ['180'],
          'message' => 'Should be less than 180'
        ],
        [
          'method' => 'gte',
          'parameters' => ['-180'],
          'message' => 'Should be greater than -180'
        ]
      ],
      'list' => [
        'format' => 'none'
      ],
      'detail' => [
        'format' => 'none'
      ],
      'default' => '0.00000000',
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Active',
      'name' => 'active',
      'field' => [ 'type' => 'active' ],
      'validation' => [],
      'list' => [ 'format' => 'hide' ],
      'detail' => [ 'format' => 'hide' ],
      'default' => 1,
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 1
    ],
    [
      'label' => 'Created',
      'name' => 'created',
      'field' => [ 'type' => 'created' ],
      'validation' => [],
      'list' => [ 'format' => 'none' ],
      'detail' => [ 'format' => 'none' ],
      'default' => 'NOW()',
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 1
    ],
    [
      'label' => 'Updated',
      'name' => 'updated',
      'field' => [ 'type' => 'updated' ],
      'validation' => [],
      'list' => [ 'format' => 'none' ],
      'detail' => [ 'format' => 'none' ],
      'default' => 'NOW()',
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 1
    ]
  ],
  'suggestion' => '{{address_contact_name}} - {{address_name}}, {{address_city}}',
];
