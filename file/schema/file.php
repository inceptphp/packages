<?php //-->
return [
  'singular' => 'File',
  'plural' => 'Files',
  'name' => 'file',
  'group' => 'Website',
  'icon' => 'fas fa-file-alt',
  'detail' => 'Manages Files',
  'fields' => [
    [
      'label' => 'Name',
      'name' => 'name',
      'field' => [
        'type' => 'text',
        'attributes' => [
          'placeholder' => 'sample.docx'
        ]
      ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Name is Required'
        ]
      ],
      'list' => [
        'format' => 'link',
        'parameters' => [
          '/download?filename={{file_name}}&location={{file_data}}',
          '{{file_name}}'
        ]
      ],
      'detail' => [
        'format' => 'link',
        'parameters' => [
          '/download?filename={{file_name}}&location={{file_data}}',
          '{{file_name}}'
        ]
      ],
      'default' => null,
      'searchable' => 1,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Description',
      'name' => 'description',
      'field' => [
        'type' => 'textarea',
        'attributes' => [
          'placeholder' => 'Describe this file'
        ]
      ],
      'validation' => [],
      'list' => ['format' => 'hide'],
      'detail' => ['format' => 'hide'],
      'default' => null,
      'searchable' => 1,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Data',
      'name' => 'data',
      'field' => [
        'type' => 'file',
        'attributes' => [
          'accept' => 'image/*,text/*,application/vnd.*,application/pdf'
        ]
      ],
      'validation' => [],
      'list' => ['format' => 'hide'],
      'detail' => ['format' => 'hide'],
      'default' => null,
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
  'suggestion' => '{{file_id}} - {{file_name}}',
  'fixtures' => [
    [
      'file_name' => 'lifestyle.jpg',
      'file_description' => 'This is Lifestyle',
      'file_data' => 'https://image.freepik.com/free-photo/healthy-lifestyle-background-with-alarm-clock-jump-rope_1428-1424.jpg'
    ]
  ]
];
