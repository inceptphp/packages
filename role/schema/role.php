<?php //-->
return [
  'singular' => 'Role',
  'plural' => 'Roles',
  'name' => 'role',
  'group' => 'Users',
  'icon' => 'fas fa-key',
  'detail' => 'By default, all users are locked out from accessing anything in the system. Roles gives users permission to access certain parts of the system based on URL rules.',
  'suggestion' => '{{role_name}}',
  'fields' => [
    [
      'label' => 'Name',
      'name' => 'name',
      'field' =>  [ 'type' => 'text' ],
      'validation' => [
        [
          'method' => 'required',
          'message' => 'Name is required'
        ]
      ],
      'list' => [ 'format' => 'none' ],
      'detail' => [ 'format' => 'none' ],
      'default' => null,
      'searchable' => 1,
      'filterable' => 0,
      'sortable' => 0,
      'disabled' => 1
    ],
    [
      'label' => 'Slug',
      'name' => 'slug',
      'field' =>  [
        'type' => 'slug',
        'attributes' => [
          'data-source' => 'input[name="role_name"]',
          'data-space' => '_',
          'data-lower' => 1
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
      'list' => [ 'format' => 'none' ],
      'detail' => [ 'format' => 'none' ],
      'default' => null,
      'searchable' => 1,
      'filterable' => 0,
      'sortable' => 0,
      'disabled' => 1
    ],
    [
      'label' => 'Locked',
      'name' => 'locked',
      'field' =>  [ 'type' => 'switch' ],
      'validation' => [],
      'list' => [ 'format' => 'yesno' ],
      'detail' => [ 'format' => 'yesno' ],
      'default' => 0,
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 1
    ],
    [
      'label' => 'Permissions',
      'name' => 'permissions',
      'field' =>  [ 'type' => 'rawjson' ],
      'validation' => [],
      'list' => [ 'format' => 'hide' ],
      'detail' => [ 'format' => 'hide' ],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0,
      'disabled' => 1
    ],
    [
      'label' => 'Admin Menu',
      'name' => 'admin_menu',
      'field' =>  [ 'type' => 'rawjson' ],
      'validation' => [],
      'list' => [ 'format' => 'hide' ],
      'detail' => [ 'format' => 'hide' ],
      'default' => null,
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0,
      'disabled' => 1
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
  'relations' => [],
  'disabled' => 1,
  'fixtures' => [
    [
      'role_name' => 'Developer',
      'role_slug' => 'developer',
      'role_locked' => 1,
      'role_permissions' => json_encode([
          [
            'path' => '**',
            'label' => 'All Access',
            'method' => 'all'
          ]
      ]),
      'role_admin_menu' => json_encode([
        [
          'label' => 'Dashboard',
          'icon' => 'fa-tachometer-alt',
          'path' => '/admin'
        ],
        [
          'label' => 'Admin',
          'icon' => 'fa-coffee',
          'path' => 'menu-admin',
          'submenu' => [
            [
              'label' => 'Profile',
              'path' => '/admin/system/object/profile/search'
            ],
            [
              'label' => 'Auth',
              'path' => '/admin/system/object/auth/search'
            ],
            [
              'label' => 'Roles',
              'path' => '/admin/system/object/role/search'
            ]
          ]
        ],
        [
          'label' => 'System',
          'icon' => 'fa-server',
          'path' => 'menu-system',
          'submenu' => [
            [
              'label' => 'Schemas',
              'path' => '/admin/system/schema/search'
            ],
            [
              'label' => 'Fieldsets',
              'path' => '/admin/system/fieldset/search'
            ]
          ]
        ],
        [
          'label' => 'Configuration',
          'icon' => 'fa-cogs',
          'path' => 'menu-configuration',
          'submenu' => [
            [
              'label' => 'Packages',
              'path' => '/admin/package/search'
            ],
            [
              'label' => 'Languages',
              'path' => '/admin/language/search'
            ],
            [
              'label' => 'Auth',
              'path' => '/admin/auth/settings'
            ],
            [
              'label' => 'Settings',
              'path' => '/admin/settings'
            ]
          ]
        ]
      ]),
      'role_active' => 1,
      'role_created' => date('Y-m-d H:i:s'),
      'role_updated' => date('Y-m-d H:i:s')
    ],
    [
      'role_name' => 'Admin',
      'role_slug' => 'admin',
      'role_locked' => 1,
      'role_permissions' => json_encode([
        [
          'path' => '/admin',
          'label' => 'Admin Dashboard',
          'method' => 'all'
        ],
        [
          'path' => '/admin/**',
          'label' => 'All Admin Access',
          'method' => 'all'
        ],
        [
          'path' => '(?!/(admin))/**',
          'label' => 'All Front End Access',
          'method' => 'all'
        ]
      ]),
      'role_admin_menu' => json_encode([
        [
          'label' => 'Dashboard',
          'icon' => 'fa-tachometer-alt',
          'path' => '/admin'
        ],
        [
          'label' => 'Admin',
          'icon' => 'fa-coffee',
          'path' => 'menu-admin',
          'submenu' => [
            [
              'label' => 'Profile',
              'path' => '/admin/system/object/profile/search'
            ],
            [
              'label' => 'Auth',
              'path' => '/admin/system/object/auth/search'
            ],
            [
              'label' => 'Roles',
              'path' => '/admin/system/object/role/search'
            ]
          ]
        ],
        [
          'label' => 'Configuration',
          'icon' => 'fa-cogs',
          'path' => 'menu-configuration',
          'submenu' => [
            [
              'label' => 'Languages',
              'path' => '/admin/language/search'
            ]
          ]
        ]
      ]),
      'role_active' => 1,
      'role_created' => date('Y-m-d H:i:s'),
      'role_updated' => date('Y-m-d H:i:s')
    ],
    [
      'role_name' => 'Guest',
      'role_slug' => 'guest',
      'role_locked' => 1,
      'role_permissions' => json_encode([
        [
          'path' => '(?!/(admin))/**',
          'label' => 'All Front End Access',
          'method' => 'all'
        ]
      ]),
      'role_admin_menu' => json_encode([]),
      'role_active' => 1,
      'role_created' => date('Y-m-d H:i:s'),
      'role_updated' => date('Y-m-d H:i:s')
    ]
  ]
];
