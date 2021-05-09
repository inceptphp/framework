<?php //-->
return array (
  'singular' => 'Foo',
  'plural' => 'Foos',
  'name' => 'foo',
  'group' => 'Bar',
  'icon' => 'fas fa-question',
  'detail' => 'Schema fixture',
  'fields' =>
  array (
    0 =>
    array (
      'label' => 'Title',
      'name' => 'title',
      'field' =>
      array (
        'type' => 'foo',
      ),
      'validation' =>
      array (
        0 =>
        array (
          'method' => 'required',
          'message' => 'Title is required',
        ),
        1 =>
        array (
          'method' => 'foo',
          'message' => 'Title is foo',
        ),
      ),
      'list' =>
      array (
        'format' => 'foo',
      ),
      'detail' =>
      array (
        'format' => 'foo',
      ),
      'default' => null,
      'searchable' => 1,
      'filterable' => 1,
      'sortable' => 1
    ),
    1 =>
    array (
      'label' => 'Formula',
      'name' => 'formula',
      'field' =>
      array (
        'type' => 'none',
      ),
      'validation' =>
      array (
      ),
      'list' =>
      array (
        'format' => 'hide',
      ),
      'detail' =>
      array (
        'format' => 'none',
      ),
      'default' => 'bar',
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 0
    ),
    2 =>
    array (
      'label' => 'Active',
      'name' => 'active',
      'field' =>
      array (
        'type' => 'active',
      ),
      'list' =>
      array (
        'format' => 'hide',
      ),
      'detail' =>
      array (
        'format' => 'hide',
      ),
      'default' => 1,
      'searchable' => 0,
      'filterable' => 1,
      'sortable' => 1
    ),
    3 =>
    array (
      'label' => 'Created',
      'name' => 'created',
      'field' =>
      array (
        'type' => 'created',
      ),
      'list' =>
      array (
        'format' => 'none',
      ),
      'detail' =>
      array (
        'format' => 'none',
      ),
      'default' => 'NOW()',
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 1
    ),
    4 =>
    array (
      'label' => 'Updated',
      'name' => 'updated',
      'field' =>
      array (
        'type' => 'updated',
      ),
      'list' =>
      array (
        'format' => 'none',
      ),
      'detail' =>
      array (
        'format' => 'none',
      ),
      'default' => 'NOW()',
      'searchable' => 0,
      'filterable' => 0,
      'sortable' => 1
    ),
  ),
  'relations' =>
  array (
  ),
  'suggestion' => '{{post_title}}',
);
