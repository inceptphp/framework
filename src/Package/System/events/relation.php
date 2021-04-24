<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Incept\Framework\Schema;
use Incept\Framework\SystemException;

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/**
 * Links model to relation
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-relation-link', function (
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
  } else {
    try {
      $schema = Schema::load($data['schema1']);
    } catch (SystemException $e) {
      $response->invalidate('schema1', 'Schema is invalid.');
    }
  }

  if (!isset($data['schema2'])) {
    $response->invalidate('schema2', 'Schema is required.');
  }

  if (!$response->isValid()) {
    return $response->setError(true, 'Invalid Parameters');
  }

  $relation = $schema->getRelations(null, $data['schema2']);

  //if no relation
  if (empty($relation)) {
    //try the other way around
    try {
      $schema = Schema::load($data['schema2']);
    } catch (SystemException $e) {
      $response->invalidate('schema2', 'Schema is invalid.');
    }

    $relation = $schema->getRelations(null, $data['schema1']);
  }

  //if still no relation
  if (empty($relation)) {
    $response->invalidate('schema2', 'Could not find a relation.');
  }

  if (!$response->isValid()) {
    return $response->setError(true, 'Invalid Parameters');
  }

  //results will look like [profile_product => object]
  $table = array_key_first($relation);
  $relation = $relation[$table];
  //get the primaries
  $primary1 = $relation->get('primary1');
  $primary2 = $relation->get('primary2');

  if (!isset($data[$primary1])) {
    $response->invalidate($primary1, 'ID is required');
  } else if (!is_numeric($data[$primary1])) {
    $response->invalidate($primary1, 'Invalid ID');
  }

  if (!isset($data[$primary2])) {
    $response->invalidate($primary2, 'ID is required');
  } else if (!is_numeric($data[$primary2])) {
    $response->invalidate($primary2, 'Invalid ID');
  }

  if (!$response->isValid()) {
    return $response->setError(true, 'Invalid Parameters');
  }

  //----------------------------//
  // 3. Prepare Data
  //make a new payload
  $payload = $request->clone(true);

  //set the payload
  $payload->setStage([
    'table' => $table,
    'data' => [
      $primary1 => $data[$primary1],
      $primary2 => $data[$primary2],
    ],
    'with_primary' => true
  ]);

  //----------------------------//
  // 4. Process Data
  try {
    $this('event')->emit('system-store-insert', $payload, $response);
  } catch (Throwable $e) {
    $response->setError(true, $e->getMessage());
  }
});

/**
 * Uninks model to relation
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-relation-unlink', function (
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
  } else {
    try {
      $schema = Schema::load($data['schema1']);
    } catch (SystemException $e) {
      $response->invalidate('schema1', 'Schema is invalid.');
    }
  }

  if (!isset($data['schema2'])) {
    $response->invalidate('schema2', 'Schema is required.');
  }

  if (!$response->isValid()) {
    return $response->setError(true, 'Invalid Parameters');
  }

  $relation = $schema->getRelations(null, $data['schema2']);

  //if no relation
  if (empty($relation)) {
    //try the other way around
    try {
      $schema = Schema::load($data['schema2']);
    } catch (SystemException $e) {
      $response->invalidate('schema2', 'Schema is invalid.');
    }

    $relation = $schema->getRelations(null, $data['schema1']);
  }

  //if still no relation
  if (empty($relation)) {
    $response->invalidate('schema2', 'Could not find a relation.');
  }

  if (!$response->isValid()) {
    return $response->setError(true, 'Invalid Parameters');
  }

  //results will look like [profile_product => object]
  $table = array_key_first($relation);
  $relation = $relation[$table];
  //get the primaries
  $primary1 = $relation->get('primary1');
  $primary2 = $relation->get('primary2');

  if (!isset($data[$primary1])) {
    $response->invalidate($primary1, 'ID is required');
  } else if (!is_numeric($data[$primary1])) {
    $response->invalidate($primary1, 'Invalid ID');
  }

  if (!isset($data[$primary2])) {
    $response->invalidate($primary2, 'ID is required');
  } else if (!is_numeric($data[$primary2])) {
    $response->invalidate($primary2, 'Invalid ID');
  }

  if (!$response->isValid()) {
    return $response->setError(true, 'Invalid Parameters');
  }

  //----------------------------//
  // 3. Prepare Data
  //make a new payload
  $payload = $request->clone(true);

  //set the payload
  $payload->setStage([
    'table' => $table,
    'filters' => [
      ['where' => $primary1 . ' = %s', 'binds' => [ $data[$primary1] ]],
      ['where' => $primary2 . ' = %s', 'binds' => [ $data[$primary2] ]]
    ]
  ]);

  //----------------------------//
  // 4. Process Data
  try {
    $this('event')->emit('system-store-delete', $payload, $response);
  } catch (Throwable $e) {
    $response->setError(true, $e->getMessage());
  }
});

/**
 * Unlinks all model from relation
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-relation-unlink-all', function (
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
  } else {
    try {
      $schema = Schema::load($data['schema1']);
    } catch (SystemException $e) {
      $response->invalidate('schema1', 'Schema is invalid.');
    }
  }

  if (!isset($data['schema2'])) {
    $response->invalidate('schema2', 'Schema is required.');
  }

  if (!$response->isValid()) {
    return $response->setError(true, 'Invalid Parameters');
  }

  $relation = $schema->getRelations(null, $data['schema2']);

  //if no relation
  if (empty($relation)) {
    //try the other way around
    try {
      $schema = Schema::load($data['schema2']);
    } catch (SystemException $e) {
      $response->invalidate('schema2', 'Schema is invalid.');
    }

    $relation = $schema->getRelations(null, $data['schema1']);
  }

  //if still no relation
  if (empty($relation)) {
    $response->invalidate('schema2', 'Could not find a relation.');
  }

  if (!$response->isValid()) {
    return $response->setError(true, 'Invalid Parameters');
  }

  //results will look like [profile_product => object]
  $table = array_key_first($relation);
  $relation = $relation[$table];
  //get the primary
  $primary = $relation->get('primary1');

  if (!isset($data[$primary])) {
    $response->invalidate($primary, 'ID is required');
  } else if (!is_numeric($data[$primary])) {
    $response->invalidate($primary, 'Invalid ID');
  }

  if (!$response->isValid()) {
    return $response->setError(true, 'Invalid Parameters');
  }

  //----------------------------//
  // 3. Prepare Data
  //eg. filters = [['where' => 'product_id =%s', 'binds' => [1]]]
  $filters = [['where' => $primary . ' = %s', 'binds' => [ $data[$primary] ]]];
  //make a new payload
  $payload = $request->clone(true);

  //set the payload
  $payload->setStage([
    'table' => $table,
    'filters' => $filters
  ]);

  //----------------------------//
  // 4. Process Data
  try {
    $this('event')->emit('system-store-delete', $payload, $response);
  } catch (Throwable $e) {
    $response->setError(true, $e->getMessage());
  }
});

/**
 * System Relation [Schema 1] Link [Schema 2] Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-relation-%s-link-%s', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema1', $meta['variables'][0]);
    $request->setStage('schema2', $meta['variables'][1]);
    $this('event')->emit('system-relation-link', $request, $response);
  }
});

/**
 * System Relation [Schema 1] Unlink [Schema 2] Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-relation-%s-unlink-%s', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema1', $meta['variables'][0]);
    $request->setStage('schema2', $meta['variables'][1]);
    $this('event')->emit('system-relation-unlink', $request, $response);
  }
});

/**
 * System Relation [Schema 1] Unlink All [Schema 2] Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-relation-%s-unlink-all-%s', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $meta = $this('event')->getEventEmitter()->getMeta();

  if (isset($meta['variables'][0])) {
    $request->setStage('schema1', $meta['variables'][0]);
    $request->setStage('schema2', $meta['variables'][1]);
    $this('event')->emit('system-relation-unlink-all', $request, $response);
  }
});
