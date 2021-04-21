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
 * System Model Create Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-object-create', function (
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

  $errors = $schema->getErrors($data);

  //if there are errors
  if (!empty($errors)) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->invalidate($errors);
  }

  //----------------------------//
  // 3. Prepare Data
  //load the emitter
  $emitter = $this('event');
  //make a new payload
  $payload = $request->clone(true);
  //dont allow to set the primary id
  unset($data[$schema->getPrimaryName()]);
  //prepare data
  $prepared = $schema->prepare($data, true);

  //set the payload
  $payload->setStage([
    'table' => $data['schema'],
    'data' => $prepared
  ]);

  //----------------------------//
  // 4. Process Data
  $emitter->call('system-store-insert', $payload, $response);

  if ($response->isError() || !$response->hasResults()) {
    return;
  }

  //get the results (which is the id)
  $id = $response->getResults();
  //get the primary name
  $primary = $schema->getPrimaryName();

  //loop through all forward relations
  foreach ($schema->getRelations() as $table => $relation) {
    //set the 2nd primary
    $primary2 = $relation['primary2'];
    //if id is invalid
    if (!isset($data[$primary2]) || !is_numeric($data[$primary2])) {
      //skip
      continue;
    }

    //allow linking an array of IDs
    if (!is_array($data[$primary2])) {
      $data[$primary2] = [$data[$primary2]];
    }

    //link relations
    $emitter->call('system-relation-link', [
      'schema1' => $data['schema'],
      'schema2' => $relation['name'],
      $primary => $id,
      //should consider array of ids
      $primary2 => $data[$primary2],
    ]);
  }

  //loop through all reverse relations
  foreach ($schema->getReverseRelations() as $table => $relation) {
    //set the 2nd primary
    $primary2 = $relation['primary2'];
    //if id is invalid
    if (!isset($data[$primary2]) || !is_numeric($data[$primary2])) {
      //skip
      continue;
    }

    //allow linking an array of IDs
    if (!is_array($data[$primary2])) {
      $data[$primary2] = [$data[$primary2]];
    }

    //link relations
    $emitter->call('system-relation-link', [
      'schema1' => $data['schema'],
      'schema2' => $relation['name'],
      $primary => $id,
      //should consider array of ids
      $primary2 => $data[$primary2],
    ]);
  }

  //lastly return the detail
  $emitter->call('system-object-detail', [ $primary => $id ], $response);
});

/**
 * System Model Detail Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-object-detail', function (
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

  if (!isset($data['join'])) {
    $data['join'] = 'forward';
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

  //get the primary name
  $primary = $schema->getPrimaryName();

  //determine key and value
  $key = $value = null;
  //the obvious thing is primary
  if (array_key_exists($primary, $data)) {
    $key = $primary;
    $value = $data[$primary];
  } else {
    //look for any unique keys
    foreach ($schema->getFields('unique') as $name => $field) {
      if (array_key_exists($name, $data)) {
        $key = $name;
        $value = $data[$name];
        break;
      }
    }
  }

  //we need an id
  if (!$value) {
    return $response->setError(true, 'Invalid ID');
  }

  //----------------------------//
  // 3. Prepare Data
  //load system package
  $system = $this('system');
  //load the emitter
  $emitter = $this('event');
  //make a new payload
  $payload = $request->clone(true);

  //get columns
  $columns = $data['columns'];
  //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
  $joins = $system->getInnerJoins($schema, $data);
  //eg. filters = [['where' => 'product_id =%s', 'binds' => [1]]]
  $filters = [['where' => $key . ' =%s', 'binds' => [$value]]];

  //set the payload
  $payload->setStage([
    'table' => $data['schema'],
    'columns' => $columns,
    'joins' => $joins,
    'filters' => $filters,
    'start' => 0,
    'range' => 1
  ]);

  //----------------------------//
  // 4. Process Data
  $results = $emitter->call('system-store-search', $payload, $response);

  if ($response->isError()) {
    return;
  }

  if (!isset($results[0])) {
    return $response->setError(true, 'Not Found');
  }

  //organize all the results
  $results = $system->deflateRow($schema, $results[0]);
  $id = $results[$primary];

  //attach forward joins
  foreach ($schema->getRelations() as $relationTable => $relation) {
    $name = $group = $relation->getName();
    $primary2 = $relation->getPrimaryName();

    $isJoin = $data['join'] === 'all'
      || $data['join'] === 'forward'
      || (
        is_array($data['join'])
        && in_array($name, $data['join'])
      );

    //we already joined 1:1, dont do it again
    //if it's not on the join list
    if ($relation['many'] == 1 || !$isJoin) {
      continue;
    }

    //case for post_post
    if ($name === $data['schema']) {
      $group = '_children';
    }

    //make a default
    $results[$group] = null;

    //make a new payload
    $payload = $request->clone(true);

    //filter settings
    $payload->setStage([
      'table' => $name,
      //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
      'joins' => [
        ['type' => 'inner', 'table' => $relationTable, 'where' => $primary2],
        ['type' => 'inner', 'table' => $data['schema'], 'where' => $primary]
      ],
      //eg. filters = [['where' => 'product_id =%s', 'binds' => [1]]]
      'filters' => [
        ['where' => $primary . ' =%s', 'binds' => [$id]]
      ],
      'range' => 0
    ]);

    //if 1:0
    if ($relation['many'] == 0) {
      //we only need one
      $payload->setStage('range', 1);
    }

    $child = $emitter->call('system-store-search', $payload);

    //if 1:0
    if ($relation['many'] == 0 && isset($child[0])) {
      //we only need one
      $results[$group] = $child[0];
      continue;
    }

    $results[$group] = $child;
  }

  //attach reverse joins
  foreach ($schema->getReverseRelations() as $relationTable => $relation) {
    $name = $relation->getName();
    $primary2 = $relation->getPrimaryName();

    $isJoin = $data['join'] === 'all'
      || $data['join'] === 'forward'
      || (
        is_array($data['join'])
        && in_array($name, $data['join'])
      );

    //only join 1:N and N:N
    //if it's not on the join list
    if ($relation['many'] < 2 || !$isJoin) {
      continue;
    }

    //make a new payload
    $payload = $request->clone(true);

    //filter settings
    $payload->setStage([
      'table' => $name,
      //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
      'joins' => [
        ['type' => 'inner', 'table' => $relationTable, 'where' => $primary2],
        ['type' => 'inner', 'table' => $data['schema'], 'where' => $primary]
      ],
      //eg. filters = [['where' => 'product_id =%s', 'binds' => [1]]]
      'filters' => [
        ['where' => $primary . ' =%s', 'binds' => [$id]]
      ],
      'range' => 0
    ]);

    $results[$name] = $emitter->call('system-store-search', $payload);
  }

  $response->setError(false)->setResults($results);
});

/**
 * Links schema to relation
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-object-link', function (
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
  if (!isset($data[$primary1]) && !is_numeric($data[$primary1])) {
    $response->invalidate($primary1, 'Invailid ID');
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
  $id1 = $data[$primary1];
  foreach ($data[$primary2] as $id2) {
    $rows[] = [ $primary1 => $id1, $primary2 => $id2 ];
  }

  //set the payload
  $payload->setStage([
    'table' => $table,
    'rows' => $rows
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
 * System Model Remove Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-object-remove', function (
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
  //load the emitter
  $emitter = $this('event');
  //get the object detail
  $emitter->emit('system-object-detail', $request, $response);

  //----------------------------//
  // 2. Validate Data
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 3. Prepare Data
  //get the schema, no need to try cuz of system-object-detail
  $schema = Schema::load($request->getStage('schema'));
  //get the primary column name
  $primary = $schema->getPrimaryName();
  //get the ID of the object
  $id = $response->getResults($primary);
  //eg. filters = [['where' => 'product_id =%s', 'binds' => [1]]]
  $filters = [['where' => $primary . ' = %s', 'binds' => [ $id ]]];

  //we will use the original as the results later
  $original = $response->getResults();
  //reset the json
  $response->remove('json');
  //make a new payload
  $payload = $request->clone(true);
  //set the payload
  $payload->setStage([
    'table' => $request->getStage('schema'),
    'filters' => $filters
  ]);

  //----------------------------//
  // 4. Process Data
  //we need active to determine if we should update or delete
  $active = $schema->getFields('active');
  //if theres an active field
  if (!empty($active)) {
    //get the active field name
    $active = array_keys($active)[0];
    $payload->setStage('data', $active, 0);
    //update
    $emitter->call('system-store-update', $payload, $response);
  //there is no active field
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
 * System Model Restore Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-object-restore', function (
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
  //load the emitter
  $emitter = $this('event');
  //get the object detail
  $emitter->emit('system-object-detail', $request, $response);

  //----------------------------//
  // 2. Validate Data
  if ($response->isError()) {
    return;
  }

  //get the schema, no need to try cuz of system-object-detail
  $schema = Schema::load($request->getStage('schema'));
  //get active
  $active = $schema->getFields('active');
  if (empty($active)) {
    return $response->setError(true, 'Cannot be restored');
  }

  //----------------------------//
  // 3. Prepare Data
  //get the primary column name
  $primary = $schema->getPrimaryName();
  //get the ID of the object
  $id = $response->getResults($primary);
  //get the active field name
  $active = array_keys($active)[0];
  //eg. filters = [['where' => 'product_id =%s', 'binds' => [1]]]
  $filters = [['where' => $primary . ' = %s', 'binds' => [ $id ]]];

  //we will use the original as the results later
  $original = $response->getResults();
  //reset the json
  $response->remove('json');
  //make a new payload
  $payload = $request->clone(true);
  //set the payload
  $payload->setStage([
    'table' => $request->getStage('schema'),
    'data' => [ $active => 1 ],
    'filters' => $filters
  ]);

  //----------------------------//
  // 4. Process Data
  $emitter->call('system-store-update', $payload, $response);

  if ($response->isError() || !$response->hasResults()) {
    return;
  }

  $response->setResults($original);
});

/**
 * Links schema to relation
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-object-unlink', function (
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
  if (!isset($data[$primary1]) && !is_numeric($data[$primary1])) {
    $response->invalidate($primary1, 'Invailid ID');
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
  $id1 = $data[$primary1];
  foreach ($data[$primary2] as $id2) {
    $where[] = sprintf('(%s = %s AND %s = %s)', $primary1, $id1, $primary2, $id2);
  }

  //eg. filters = [['where' => 'product_id =%s', 'binds' => [1]]]
  $filters = [['where' => implode(' OR ', $where), 'binds' => []]];

  //set the payload
  $payload->setStage([
    'table' => $table,
    'filter' => $filters
  ]);

  //----------------------------//
  // 4. Process Data
  $emitter->call('system-store-delete', $payload, $response);

  if ($response->isError()) {
    return;
  }

  $results = $request->getStage();
  $response->setError(false)->setResults([
    $primary1 => $request->getStage($primary1),
    $primary2 => $request->getStage($primary2)
  ]);
});

/**
 * System Model Update Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-object-update', function (
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

  //load the emitter
  $emitter = $this('event');
  //get the object detail
  $emitter->emit('system-object-detail', $request, $response);

  //----------------------------//
  // 2. Validate Data
  if ($response->isError()) {
    return;
  }

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
  //get the primary column name
  $primary = $schema->getPrimaryName();
  //get the ID of the object
  $id = $response->getResults($primary);
  //eg. filters = [['where' => 'product_id =%s', 'binds' => [1]]]
  $filters = [['where' => $primary . ' = %s', 'binds' => [ $id ]]];
  //prepare data
  $prepared = $schema->prepare($data);
  //dont allow to update the primary id
  unset($prepared[$schema->getPrimaryName()]);

  //we will use the original as the results later
  $original = $response->getResults();
  //reset the json
  $response->remove('json');
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
  $emitter->call('system-store-update', $payload, $response);

  if ($response->isError() || !$response->hasResults()) {
    return;
  }

  $data['original'] = $original;
  $response->setResults($data);
});

/**
 * System Model [Schema] Create Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-object-%s-create', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-object-create', $request, $response);
  }
});

/**
 * System Model [Schema] Detail Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-object-%s-detail', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-object-detail', $request, $response);
  }
});

/**
 * System Model [Schema] Link Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-object-%s-link-%s', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema1', $meta['variables'][0]);
    $request->setStage('schema2', $meta['variables'][1]);
    $this('event')->emit('system-object-link', $request, $response);
  }
});

/**
 * System Model [Schema] Remove Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-object-%s-remove', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-object-remove', $request, $response);
  }
});

/**
 * System Model [Schema] Restore Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-object-%s-restore', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-object-restore', $request, $response);
  }
});

/**
 * System Model [Schema] Unlink Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-object-%s-unlink-%s', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema1', $meta['variables'][0]);
    $request->setStage('schema2', $meta['variables'][1]);
    $this('event')->emit('system-object-unlink', $request, $response);
  }
});

/**
 * System Model [Schema] Update Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-object-%s-update', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema', $meta['variables'][0]);
    $this('event')->emit('system-object-update', $request, $response);
  }
});
