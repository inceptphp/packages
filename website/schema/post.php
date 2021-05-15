<?php //-->
return [
  'singular' => 'Post',
  'plural' => 'Posts',
  'name' => 'post',
  'group' => 'Website',
  'icon' => 'fas fa-pencil-alt',
  'detail' => 'Manages general posts for the website',
  'fields' => [
    [
      'label' => 'Banner',
      'name' => 'banner',
      'field' => [
        'type' => 'image'
      ],
      'validation' => [],
      'list' => [
        'format' => 'image',
        'parameters' => [0, 50]
      ],
      'detail' => [
        'format' => 'image',
        'parameters' => ['100%', 0]
      ],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Title',
      'name' => 'title',
      'field' => ['type' => 'text'],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Title is required'
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
      'label' => 'Slug',
      'name' => 'slug',
      'field' => [
        'type' => 'slug',
        'attributes' => [
          'data-source' => 'input[name=post_title]'
        ]
      ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Slug is required'
        ],
        [
          'method' => 'unique',
          'message' => 'Should be unique'
        ]
      ],
      'list' => ['format' => 'hide'],
      'detail' => ['format' => 'hide'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Summary',
      'name' => 'summary',
      'field' => [
        'type' => 'textarea'
      ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Summary is required'
        ],
        [
          'method' => 'char_lte',
          'parameters' => ['160'],
          'message' => 'Should be less than 160 characters'
        ]
      ],
      'list' => ['format' => 'none'],
      'detail' => ['format' => 'none'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Detail',
      'name' => 'detail',
      'field' => [
        'type' => 'wysiwyg',
        'attributes' => [
          'rows' => 15
        ]
      ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Detail is required'
        ]
      ],
      'list' => ['format' => 'hide'],
      'detail' => ['format' => 'html'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Tags',
      'name' => 'tags',
      'field' => ['type' => 'taglist'],
      'validation' => [],
      'list' => ['format' => 'taglist'],
      'detail' => ['format' => 'taglist'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Meta',
      'name' => 'meta',
      'field' => ['type' => 'meta'],
      'validation' => [],
      'list' => ['format' => 'hide'],
      'detail' => ['format' => 'meta'],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0
    ],
    [
      'label' => 'Status',
      'name' => 'status',
      'field' => [
        'type' => 'select',
        'options' => [
          'pending' => 'Pending',
          'approved' => 'Approved'
        ]
      ],
      'validation' => [
        [
          'method' => 'one',
          'parameters' => [ 'pending', 'approved' ],
          'message' => 'Should be one of pending, approved'
        ]
      ],
      'list' => ['format' => 'lower'],
      'detail' => ['format' => 'lower'],
      'default' => 'pending',
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 0
    ],
    [
      'label' => 'Published',
      'name' => 'published',
      'field' => [
        'type' => 'datetime'
      ],
      'validation' => [],
      'list' => [
        'format' => 'date',
        'parameters' => ['F d, Y g:iA']
      ],
      'detail' => [
        'format' => 'date',
        'parameters' => ['F d, Y g:iA']
      ],
      'default' => null,
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 1
    ],
    [
      'label' => 'Public',
      'name' => 'public',
      'field' => ['type' => 'switch'],
      'validation' => [],
      'list' => ['format' => 'yesno'],
      'detail' => ['format' => 'yesno'],
      'default' => 1,
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 1
    ],
    [
      'label' => 'Show Comments',
      'name' => 'comments',
      'field' => ['type' => 'switch'],
      'validation' => [
        [
          'method' => 'one',
          'parameters' => ['0', '1'],
          'message' => 'Should be either 0 or 1'
        ]
      ],
      'list' => ['format' => 'yesno'],
      'detail' => ['format' => 'yesno'],
      'default' => 1,
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 1
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
  'relations' => [
    [
      'many' => 1,
      'name' => 'profile'
    ],
    [
      'many' => 2,
      'name' => 'post'
    ],
    [
      'many' => 2,
      'name' => 'category'
    ],
    [
      'many' => 2,
      'name' => 'comment'
    ],
    [
      'many' => 2,
      'name' => 'file'
    ]
  ],
  'suggestion' => '{{post_title}}',
];
