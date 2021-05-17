<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Incept\Framework\Schema;
use Incept\Framework\SystemException;

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/**
 * System Collection Create Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-collection-create', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  $data = [];
  if ($request->hasStage()) {
    $data = $request->getStage();
  }

  //load system package
  $system = $this('system');
  //remove the data that is blank
  $data = $system->cleanData($data);

  //----------------------------//
  // 2. Validate Data
  //must have schema
  if (!isset($data['schema'])) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->invalidate('schema', 'Schema is required.');
  }

  //must have rows
  if (!isset($data['rows'])
    || !is_array($data['rows'])
    || empty($data['rows'])
  ) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->invalidate('rows', 'Missing rows.');
  }

  try { //to load schema
    $schema = Schema::load($data['schema']);
  } catch (SystemException $e) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->invalidate('schema', $e->getMessage());
  }

  //get errors per row
  foreach ($data['rows'] as $i => $row) {
    $error = $schema->getErrors($row);
    //if there is an error
    if (!empty($error)) {
      //add it to the errors list
      $response->invalidate('rows', $i, $error);
    }
  }

  //if there are errors
  if (!$response->isValid()) {
    return $response->setError(true, 'Invalid Parameters');
  }

  //----------------------------//
  // 3. Prepare Data
  //load the emitter
  $emitter = $this('event');
  //make a new payload
  $payload = $request->clone(true);

  //get the primary name
  $primary = $schema->getPrimaryName();

  //for each row
  foreach ($data['rows'] as $i => $row) {
    //prepare the data
    $data['rows'][$i] = $schema->prepare($row, true);
    //dont allow to insert the primary id
    unset($data['rows'][$i][$primary]);
  }

  //set the payload
  $payload->setStage([
    'table' => $data['schema'],
    'rows' => $data['rows']
  ]);

  //----------------------------//
  // 4. Process Data
  $emitter->call('system-store-insert', $payload, $response);

  if ($response->isError() || !$response->hasResults()) {
    return;
  }

  //get the last id
  $lastId = $response->getResults();

  foreach ($data['rows'] as $i => $row) {
    //re insert the id into the rows
    //ex. 10 is the last id and there are 3 rows
    // 1st = 10 - (3 - (0 + 1)) = 8
    // 2nd = 10 - (3 - (1 + 1)) = 9
    // 3rd = 10 - (3 - (2 + 1)) = 10
    if (!isset($data['rows'][$i][$primary])) {
      $data['rows'][$i][$primary] = $lastId - (count($data['rows']) - ($i + 1));
    }

    $row[$primary] = $data['rows'][$i][$primary];

    //next we need to consider all the relations

    //loop through all forward relations
    foreach ($schema->getRelations() as $table => $relation) {
      //set the 2nd primary
      $primary2 = $relation['primary2'];
      //if id is invalid
      if (!isset($row[$primary2]) || !is_numeric($row[$primary2])) {
        //skip
        continue;
      }

      //link relations
      //NOTE: PONS (loop in loop db call)
      $emitter->call('system-relation-link', [
        'schema1' => $data['schema'],
        'schema2' => $relation['name'],
        $primary => $row[$primary],
        $primary2 => $row[$primary2],
      ]);
    }

    //loop through all reverse relations
    foreach ($schema->getReverseRelations() as $table => $relation) {
      //set the 2nd primary
      $primary2 = $relation['primary2'];
      //if id is invalid
      if (!isset($row[$primary2]) || !is_numeric($row[$primary2])) {
        //skip
        continue;
      }

      //link relations
      $emitter->call('system-relation-link', [
        'schema1' => $relation['name'],
        'schema2' => $data['schema'],
        $primary => $row[$primary],
        $primary2 => $row[$primary2],
      ]);
    }
  }

  $response->setError(false)->setResults('rows', $data['rows']);
});

/**
 * Links schema to relation
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-collection-link', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  //get data from stage
  $data = [];
  if ($request->hasStage()) {
    $data = $request->getStage();
  }

  //----------------------------//
  // 2. Validate Data
  if (!isset($data['schema1'])) {
    $response->invalidate('schema1', 'Schema is required.');
  }

  try {
    $schema = Schema::load($data['schema1']);
  } catch (SystemException $e) {
    $response->invalidate('schema1', $e->getMessage());
  }

  try {
    $relation = $schema->getRelations(null, $data['schema2']);
  } catch (SystemException $e) {
    $response->invalidate('schema2', $e->getMessage());
  }

  //if no relation
  if (empty($relation)) {
    //try the other way around
    try {
      $schema = Schema::load($data['schema1']);
    } catch (SystemException $e) {
      $response->invalidate('schema2', $e->getMessage());
    }

    $relation = $schema->getRelations(null, $data['schema1']);
  }

  //if no relation
  if (empty($relation)) {
    return $response->setError(true, 'No relation.');
  }

  //get the relation table
  $table = array_keys($relation)[0];
  //single out the relation
  $relation = array_values($relation)[0];

  $primary1 = $relation['primary1'];
  //ID should be set
  if (!isset($data[$primary1])) {
    $response->invalidate($primary1, 'Invailid ID');
  } else {
    //make sure we are dealing with an array
    if (!is_array($data[$primary1])) {
      $data[$primary1] = [$data[$primary1]];
    }

    //make sure all IDs are numbers
    foreach ($data[$primary1] as $id) {
      if (!is_numeric($id)) {
        $response->invalidate($primary1, 'Invailid ID');
        break;
      }
    }
  }

  $primary2 = $relation['primary2'];
  //ID should be set
  if (!isset($data[$primary2])) {
    $response->invalidate($primary2, 'Invailid ID');
  } else {
    //make sure we are dealing with an array
    if (!is_array($data[$primary2])) {
      $data[$primary2] = [$data[$primary2]];
    }

    //make sure all IDs are numbers
    foreach ($data[$primary2] as $id) {
      if (!is_numeric($id)) {
        $response->invalidate($primary2, 'Invailid ID');
        break;
      }
    }
  }

  //if there are errors
  if (!$response->isValid()) {
    return $response->setError(true, 'Invalid Parameters');
  }

  //----------------------------//
  // 3. Prepare Data
  //load the emitter
  $emitter = $this('event');
  //make a new payload
  $payload = $request->clone(true);

  $rows = [];
  //make all combinations
  foreach ($data[$primary1] as $id1) {
    foreach ($data[$primary2] as $id2) {
      $rows[] = [ $primary1 => $id1, $primary2 => $id2 ];
    }
  }

  //set the payload
  $payload->setStage([
    'table' => $table,
    'rows' => $rows,
    'with_primary' => true
  ]);

  //----------------------------//
  // 4. Process Data
  $emitter->call('system-store-insert', $payload, $response);

  if ($response->isError()) {
    return;
  }

  $response->setError(false)->setResults([
    $primary1 => $request->getStage($primary1),
    $primary2 => $request->getStage($primary2)
  ]);
});

/**
 * System Collection Remove Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-collection-remove', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  $data = $request->getStage();

  //----------------------------//
  // 2. Validate Data
  try { //to load schema
    $schema = Schema::load($request->getStage('schema'));
  } catch (SystemException $e) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->invalidate('schema', $e->getMessage());
  }

  //----------------------------//
  // 3. Prepare Data
  //load system package
  $system = $this('system');
  //load the emitter
  $emitter = $this('event');
  //make a new payload
  $payload = $request->clone(true);

  //we will use the original as the results later
  $original = $response->getResults();
  //get the primary column name
  $primary = $schema->getPrimaryName();
  //get the ID of the object
  $ids = $request->getStage($primary);
  //we need active to determine if we should update or delete
  $active = $schema->getFields('active');
  //eg. in = [product_id => [1, 2, 3], product_meta.ref1 => [1, 2, 3]]
  $filters = $system->mapIns([ $primary => $ids ], []);

  //set the payload
  $payload->setStage([
    'table' => $request->getStage('schema'),
    'filters' => $filters
  ]);

  //----------------------------//
  // 4. Process Data
  if (!empty($active)) {
    //get the active field name
    $active = array_keys($active)[0];
    $payload->setStage('data', $active, 0);
    //update
    $emitter->call('system-store-update', $payload, $response);
  } else {
    //delete
    $emitter->call('system-store-delete', $payload, $response);
  }

  if ($response->isError() || !$response->hasResults()) {
    return;
  }

  $response->setError(false)->setResults($original);
});

/**
 * System Collection Restore Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-collection-restore', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  $data = $request->getStage();

  //----------------------------//
  // 2. Validate Data
  try { //to load schema
    $schema = Schema::load($request->getStage('schema'));
  } catch (SystemException $e) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->invalidate('schema', $e->getMessage());
  }

  //get active
  $active = $schema->getFields('active');
  if (empty($active)) {
    return $response->setError(true, 'Cannot be restored');
  }

  //get the primary column name
  $primary = $schema->getPrimaryName();

  //if no valid primary value
  if (!isset($data[$primary])
    || !is_array($data[$primary])
    || empty($data[$primary])
  ) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->invalidate($primary, 'Invalid Format');
  }

  //----------------------------//
  // 3. Prepare Data
  //load system package
  $system = $this('system');
  //make a new payload
  $payload = $request->clone(true);
  //get the active field name
  $active = array_keys($active)[0];
  //eg. in = [product_id => [1, 2, 3]
  $filters = $system->mapIns([ $primary => $data[$primary] ], []);

  //set the payload
  $payload->setStage([
    'table' => $request->getStage('schema'),
    'data' => [ $active => 1 ],
    'filters' => $filters
  ]);

  //----------------------------//
  // 4. Process Data
  $this('event')->call('system-store-update', $payload, $response);
});

/**
 * System Collection Search Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-collection-search', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError() || $response->hasResults()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  $data = [];
  if ($request->hasStage()) {
    $data = $request->getStage();
  }

  //allow columns
  if (!isset($data['columns'])) {
    $data['columns'] = '*';
  }

  if (!isset($data['sort'])) {
    $data['sort'] = [];
  }

  if (!isset($data['start'])) {
    $data['start'] = 0;
  }

  if (!isset($data['range'])) {
    $data['range'] = 50;
  }

  if (!isset($data['total'])) {
    $data['total'] = 2;
  }

  //----------------------------//
  // 2. Validate Data
  //must have schema
  if (!isset($data['schema'])) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->invalidate('schema', 'Schema is required.');
  }

  try { //to load schema
    $schema = Schema::load($data['schema']);
  } catch (SystemException $e) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->invalidate('schema', $e->getMessage());
  }

  //we need active to determine if we should add a filter
  $active = $schema->getFields('active');
  if (!empty($active)) {
    //get the active field name
    $active = array_keys($active)[0];
    //if no primary filter
    if (!isset($data['filter'][$active])) {
      //get all active rows
      $data['filter'][$active] = 1;
    //but if it's -1,
    } else if ($data['filter'][$active] === -1) {
      //they mean to get everything
      unset($data['filter'][$active]);
    }
  }

  //----------------------------//
  // 3. Prepare Data
  //load system package
  $system = $this('system');
  //load the emitter
  $emitter = $this('event');
  //make an input
  $input = $request->clone(true);

  //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
  $joins = $system->getInnerJoins($schema, $data);
  //from: filter[product_id]=1
  //to: filters = [['where' => 'product_id =%s', 'binds' => [1]]]
  $filters = $system->mapQuery($schema, $data);

  //set the payload
  $input->setStage([
    'table' => $data['schema'],
    'columns' => $data['columns'],
    'joins' => $joins,
    'filters' => $filters,
    'sort' => $data['sort'],
    'start' => $data['start'],
    'range' => $data['range']
  ]);

  if (isset($data['group'])) {
    //eg. group = ['product_id']
    $input->setStage('group', $data['group']);
  }

  if (isset($data['having'])) {
    //eg. having = [['where' => 'product_id =%s', 'binds' => [1]]]
    $having = $system->mapQuery($schema, $data['having']);
    $input->setStage('having', $having);
  }

  //----------------------------//
  // 4. Process Data
  //if not exclusively total
  if ($data['total'] !== 1) {
    //set the columns
    $input->setStage('columns', $data['columns']);
    //make an output
    $output = $response->clone(true);
    //get the rows
    $rows = $emitter->call('system-store-search', $input, $output);

    //if there's an error
    if ($output->isError()) {
      //copy the json
      return $response->set('json', $output->get('json'));
    }

    //for each row
    foreach ($rows as $i => $row) {
      //change json string to array
      $rows[$i] = $system->deflateRow($schema, $row);
    }

    //set the rows overall in the main results
    $response->setResults('rows', $rows);
  }

  //if total
  if ($data['total'] > 0) {
    //set the count column
    $input->setStage('columns', sprintf(
      'COUNT(%s) AS total',
      $schema->getPrimaryName()
    ));

    //make an output
    $output = $response->clone(true);
    //get the total
    $total = $emitter->call('system-store-search', $input, $output);
    //if there's an error
    if ($output->isError()) {
      //copy the json
      return $response->set('json', $output->get('json'));
    }

    //set the total overall in the main results
    $response->setResults('total', $total[0]['total']);
  }

  //no error
  $response->setError(false);
});

/**
 * Unlinks schema to relation
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-collection-unlink', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  //get data from stage
  $data = [];
  if ($request->hasStage()) {
    $data = $request->getStage();
  }

  //----------------------------//
  // 2. Validate Data
  if (!isset($data['schema1'])) {
    $response->invalidate('schema1', 'Schema is required.');
  }

  try {
    $schema = Schema::load($data['schema1']);
  } catch (SystemException $e) {
    $response->invalidate('schema1', $e->getMessage());
  }

  try {
    $relation = $schema->getRelations(null, $data['schema2']);
  } catch (SystemException $e) {
    $response->invalidate('schema2', $e->getMessage());
  }

  //if no relation
  if (empty($relation)) {
    //try the other way around
    try {
      $schema = Schema::load($data['schema1']);
    } catch (SystemException $e) {
      $response->invalidate('schema2', $e->getMessage());
    }

    $relation = $schema->getRelations(null, $data['schema1']);
  }

  //if no relation
  if (empty($relation)) {
    return $response->setError(true, 'No relation.');
  }

  //get the relation table
  $table = array_keys($relation)[0];
  //single out the relation
  $relation = array_values($relation)[0];

  $primary1 = $relation['primary1'];
  //ID should be set
  if (!isset($data[$primary1])) {
    $response->invalidate($primary1, 'Invailid ID');
  } else {
    //make sure we are dealing with an array
    if (!is_array($data[$primary1])) {
      $data[$primary1] = [$data[$primary1]];
    }

    //make sure all IDs are numbers
    foreach ($data[$primary1] as $id) {
      if (!is_numeric($id)) {
        $response->invalidate($primary1, 'Invailid ID');
        break;
      }
    }
  }

  $primary2 = $relation['primary2'];
  //ID should be set
  if (!isset($data[$primary2])) {
    $response->invalidate($primary2, 'Invailid ID');
  } else {
    //make sure we are dealing with an array
    if (!is_array($data[$primary2])) {
      $data[$primary2] = [$data[$primary2]];
    }

    //make sure all IDs are numbers
    foreach ($data[$primary2] as $id) {
      if (!is_numeric($id)) {
        $response->invalidate($primary2, 'Invailid ID');
        break;
      }
    }
  }

  //if there are errors
  if (!$response->isValid()) {
    return $response->setError(true, 'Invalid Parameters');
  }

  //----------------------------//
  // 3. Prepare Data
  //load the emitter
  $emitter = $this('event');
  //make a new payload
  $payload = $request->clone(true);

  $where = [];
  //make all combinations
  foreach ($data[$primary1] as $id1) {
    foreach ($data[$primary2] as $id2) {
      $where[] = sprintf('(%s = %s AND %s = %s)', $primary1, $id1, $primary2, $id2);
    }
  }

  //eg. filters = [['where' => 'product_id =%s', 'binds' => [1]]]
  $filters = [['where' => implode(' OR ', $where), 'binds' => []]];

  //set the payload
  $payload->setStage([
    'table' => $table,
    'filters' => $filters
  ]);

  //----------------------------//
  // 4. Process Data
  $emitter->call('system-store-delete', $payload, $response);

  if ($response->isError()) {
    return;
  }

  $response->setError(false)->setResults([
    $primary1 => $request->getStage($primary1),
    $primary2 => $request->getStage($primary2)
  ]);
});

/**
 * System Collection Update Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-collection-update', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  $data = [];
  if ($request->hasStage()) {
    $data = $request->getStage();
  }

  //load system package
  $system = $this('system');
  //remove the data that is blank
  $data = $system->cleanData($data);

  //----------------------------//
  // 2. Validate Data
  try { //to load schema
    $schema = Schema::load($data['schema']);
  } catch (SystemException $e) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->invalidate('schema', $e->getMessage());
  }

  $errors = $schema->getErrors($data, true);

  //if there are errors
  if (!empty($errors)) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->invalidate($errors);
  }

  //----------------------------//
  // 3. Prepare Data
  //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
  $joins = $system->getInnerJoins($schema, $data);
  //from: filter[product_id]=1
  //to: filters = [['where' => 'product_id =%s', 'binds' => [1]]]
  $query = $system->getQuery($data);
  $filters = $system->mapQuery($schema, $query);
  //remove filters from the dataset
  foreach ($query as $key => $filter) {
    if (isset($data[$key])) {
      unset($data[$key]);
    }
  }
  //prepare data
  $prepared = $schema->prepare($data);
  //dont allow to update the primary id
  $primary = $schema->getPrimaryName();
  unset($prepared[$primary]);

  //we will use the original as the results later
  $original = $response->getResults();
  //make a new payload
  $payload = $request->clone(true);
  //set the payload
  $payload->setStage([
    'table' => $data['schema'],
    'data' => $prepared,
    'filters' => $filters
  ]);

  //----------------------------//
  // 4. Process Data
  $this('event')->call('system-store-update', $payload, $response);

  if ($response->isError() || !$response->hasResults()) {
    return;
  }

  $data['original'] = $original;
  $response->setResults($data);
});

/**
 * System Collection [Schema] Create Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-collection-%s-create', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-collection-create', $request, $response);
  }
});

/**
 * System Collection [Schema] Link Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-collection-%s-link-%s', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema1', $meta['variables'][0]);
    $request->setStage('schema2', $meta['variables'][1]);
    $this('event')->emit('system-collection-link', $request, $response);
  }
});

/**
 * System Collection [Schema] Remove Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-collection-%s-remove', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-collection-remove', $request, $response);
  }
});

/**
 * System Collection [Schema] Restore Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-collection-%s-restore', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-collection-restore', $request, $response);
  }
});

/**
 * System Collection [Schema] Search Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-collection-%s-search', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-collection-search', $request, $response);
  }
});

/**
 * System Collection [Schema] Unlink Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-collection-%s-unlink-%s', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema1', $meta['variables'][0]);
    $request->setStage('schema2', $meta['variables'][1]);
    $this('event')->emit('system-collection-ulink', $request, $response);
  }
});

/**
 * System Collection [Schema] Update Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-collection-%s-update', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-collection-update', $request, $response);
  }
});
