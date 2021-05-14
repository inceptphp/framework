<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Framework\Package\System;

use UGComponents\IO\Request\RequestInterface;
use UGComponents\Package\Package;

use Incept\Framework\Framework;
use Incept\Framework\Schema;
use Incept\Framework\Fieldset;

/**
 * System Package
 *
 * @vendor   Incept
 * @package  Package
 * @standard PSR-2
 */
class SystemPackage
{
  /**
   * @var *PackageHandler $handler
   */
  protected $handler;

  /**
   * Add handler for scope when routing
   *
   * @param *PackageHandler $handler
   */
  public function __construct(Framework $handler)
  {
    $this->handler = $handler;
  }

  /**
   * Removes all null or empty data
   *
   * @param array $data
   *
   * @return array
   */
  public function cleanData(array $data): array
  {
    $hash = array_keys($data) !== range(0, count($data) - 1);
    foreach ($data as $key => $value) {
      if (is_array($value)) {
        $value = $this->cleanData($value);
        if (empty($value)) {
          unset($data[$key]);
          continue;
        }

        $data[$key] = $value;
        continue;
      }

      if (is_null($value) || $value === '') {
        unset($data[$key]);
      }
    }

    if (!$hash) {
      $data = array_values($data);
    }

    return $data;
  }

  /**
   * Removes possible SQL injections
   *
   * @param array $query
   *
   * @return array
   */
  public function cleanQuery(array $query): array
  {
    //eg. filter = [product_id => 1, product_meta.ref1 => 123]
    //eg. like = [product_id => 1, product_meta.ref1 => 123]
    //eg. in = [product_id => [1, 2, 3], product_meta.ref1 => [1, 2, 3]]
    //eg. span = [product_id => [1, 10], product_meta.ref1 => [1, 10]]
    foreach ([ 'filter', 'like', 'in', 'span'] as $dirty) {
      if (!isset($query[$dirty])) {
        continue;
      }

      foreach ($query[$dirty] as $key => $value) {
        $noValue = !is_numeric($value) && (!$value || empty($value));
        //if invalid key format or there is no value
        if ($noValue || !preg_match('/^[a-zA-Z0-9_\.]+$/', $key)) {
          unset($query[$dirty][$key]);
          continue;
        }

        if ($dirty === 'span') {
          if (isset($query['span'][$key][0])
            && !is_numeric($query['span'][$key][0])
            && !$query['span'][$key][0]
          ) {
            unset($query['span'][$key][0]);
          }

          if (isset($query['span'][$key][1])
            && !is_numeric($query['span'][$key][1])
            && !$query['span'][$key][1]
          ) {
            unset($query['span'][$key][1]);
          }
        }
      }
    }

    foreach ([ 'empty', 'nempty' ] as $dirty) {
      if (!isset($query[$dirty])) {
        continue;
      }

      foreach ($query[$dirty] as $key => $value) {
        $noValue = !$value || empty($value);
        //if invalid key format or there is no value
        if ($noValue || !preg_match('/^[a-zA-Z0-9_\.]+$/', $value)) {
          unset($query[$dirty][$key]);
        }
      }
    }

    //eg. order = [product_id => DESC
    if (isset($query['order']) && is_array($query['order'])) {
      foreach ($query['order'] as $key => $value) {
        if (!preg_match('/^[a-zA-Z0-9_\.]+$/', $key)) {
          unset($query['order'][$key]);
        }
      }
    }

    if (isset($query['start']) && !is_numeric($query['start'])) {
      unset($query['start']);
    }

    if (isset($query['range']) && !is_numeric($query['range'])) {
      unset($query['range']);
    }

    return $query;
  }

  /**
   * Removes possible SQL injections
   *
   * @param RequestInterface $request
   *
   * @return array
   */
  public function cleanStage(RequestInterface $request): RequestInterface
  {
    $clean = $this->cleanQuery($request->getStage());
    $request->set('stage', $clean);
    return $request;
  }

  /**
   * Groups results by their table prefix ie. [table]_column
   *
   * @param mixed $filter
   *
   * @return array
   */
  public function deflateRow(Schema $schema, array $row): array
  {
    //get valid json fields
    $jsons = array_keys($schema->getFields('json'));

    foreach ($row as $key => $value) {
      //name should be a json column type
      if (!in_array($key, $jsons) || is_array($row[$key])) {
        continue;
      }

      $row[$key] = json_decode($row[$key], true);
    }

    return $row;
  }

  /**
   * Generates an inner join clause
   *
   * @param mixed $filter
   *
   * @return array
   */
  public function getInnerJoins(Schema $schema, array $data): array
  {
    $joins = [];
    $primary = $schema->getPrimaryName();

    if (!isset($data['join'])) {
      $data['join'] = 'forward';
    }

    foreach ($schema->getRelations() as $table => $relation) {
      $name = $relation->get('name');
      $many = $relation->get('many');

      $primary1 = $relation->get('primary1');
      $primary2 = $relation->get('primary2');

      $isRecursive = $name === $schema->getName();

      $isFilter = isset($data['filter'][$primary2])
        || ($isRecursive && isset($data['filter'][$primary1]));

      $isLike = isset($data['like'][$primary2])
        || ($isRecursive && isset($data['like'][$primary1]));

      $isIn = isset($data['in'][$primary2])
        || ($isRecursive && isset($data['in'][$primary1]));

      $isSpan = isset($data['span'][$primary2])
        || ($isRecursive && isset($data['span'][$primary1]));

      $isJoin = (
        $many === 1 && (
          $data['join'] === 'all' || $data['join'] === 'forward')
        )
        || (
          is_array($data['join']) && in_array($name, $data['join'])
        );

      $isEmpty = isset($data['empty'])
        && is_array($data['empty'])
        && in_array($primary2, $data['empty']);

      $isNempty = isset($data['nempty'])
        && is_array($data['nempty'])
        && in_array($primary2, $data['nempty']);

      if (!$isJoin && !$isFilter && !$isLike && !$isIn && !$isEmpty && !$isNempty) {
        continue;
      }
      //eg. post_post ON (product_id = product_id_2)
      if ($isRecursive) {
        $joins[] = [
          'type' => 'inner',
          'table' => $table,
          'where' => sprintf('%s = %s', $primary2, $schema->getPrimaryName())
        ];
        continue;
      }

      //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
      $joins[] = ['type' => 'inner', 'table' => $table, 'where' => $primary1];
      $joins[] = ['type' => 'inner', 'table' => $name, 'where' => $primary2];
    }

    foreach ($schema->getReverseRelations() as $table => $relation) {
      $name = $relation->get('name');
      $many = $relation->get('many');
      $primary1 = $relation->get('primary1');
      $primary2 = $relation->get('primary2');

      //ignore post_post for example because it's already covered
      if ($name === $schema->getName()) {
        continue;
      }

      $isFilter = isset($data['filter'][$primary1]);
      $isLike = isset($data['filter'][$primary1]);
      $isIn = isset($data['filter'][$primary1]);
      $isSpan = isset($data['filter'][$primary1]);

      $isJoin = (
        $many === 1 && (
          $data['join'] === 'all' || $data['join'] === 'reverse')
        )
        || (
          is_array($data['join']) && in_array($name, $data['join'])
        );

      $isEmpty = isset($data['empty'])
        && is_array($data['empty'])
        && in_array($primary1, $data['empty']);

      $isNempty = isset($data['nempty'])
        && is_array($data['nempty'])
        && in_array($primary1, $data['nempty']);

      if (!$isJoin && !$isFilter && !$isLike && !$isIn && !$isEmpty && !$isNempty) {
        continue;
      }

      //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
      $joins[] = [ 'type' => 'inner', 'table' => $table, 'where' => $primary2 ];
      $joins[] = [ 'type' => 'inner', 'table' => $name, 'where' => $primary1 ];
    }

    return $joins;
  }

  /**
   * Returns just the filters
   *
   * @param mixed $data
   *
   * @return array
   */
  public function getQuery(array $data): array
  {
    $filters = [];

    if (isset($data['q'])) {
      $filters['q'] = $data['q'];
    }

    if (isset($data['filter'])) {
      $filters['filter'] = $data['filter'];
    }

    if (isset($data['like'])) {
      $filters['like'] = $data['like'];
    }

    if (isset($data['in'])) {
      $filters['in'] = $data['in'];
    }

    if (isset($data['span'])) {
      $filters['span'] = $data['span'];
    }

    if (isset($data['empty'])) {
      $filters['empty'] = $data['empty'];
    }

    if (isset($data['nempty'])) {
      $filters['nempty'] = $data['nempty'];
    }

    if (isset($data['order'])) {
      $filters['order'] = $data['order'];
    }

    if (isset($data['start'])) {
      $filters['start'] = $data['start'];
    }

    if (isset($data['range'])) {
      $filters['range'] = $data['range'];
    }

    return $this->cleanQuery($filters);
  }

  /**
   * Translates safe query to serialized filters
   *
   * @param array $query
   *
   * @return array
   */
  public function mapQuery(Schema $schema, array $query = []): array
  {
    //get valid json fields
    $jsons = array_keys($schema->getFields('json'));
    //eg. map = [['where' => 'product_id =%s', 'binds' => [1]]]
    $map = [];
    if (isset($query['filters']) && is_array($query['filters'])) {
      $map = $query['filters'];
    }

    //consider q
    //eg. q = 123
    if (isset($query['q'])) {
      $searchable = $schema->getFields('searchable');
      $keywords = $query['q'];
      if (!is_array($keywords)) {
        $keywords = [ $keywords ];
      }

      $map = array_merge($map, $this->mapKeywords($keywords, $searchable));
    }

    //consider filters
    //eg. filter = [product_id => 1, product_meta.ref1 => 123]
    if (isset($query['filter'])) {
      $map = array_merge($map, $this->mapFilters($query['filter'], $jsons));
    }

    //consider like
    //eg. like = [product_id => 1, product_meta.ref1 => 123]
    if (isset($query['like'])) {
      $map = array_merge($map, $this->mapLikes($query['like'], $jsons));
    }

    //consider in
    //eg. in = [product_id => [1, 2, 3], product_meta.ref1 => [1, 2, 3]]
    if (isset($query['in'])) {
      $map = array_merge($map, $this->mapIns($query['in'], $jsons));
    }

    //consider span
    //eg. span = [product_id => [1, 10], product_meta.ref1 => [1, 10]]
    if (isset($query['span'])) {
      $map = array_merge($map, $this->mapSpans($query['span'], $jsons));
    }

    //consider null
    //eg. empty = [product_id, product_meta.ref1]
    if (isset($query['empty'])) {
      $map = array_merge($map, $this->mapEmpties($query['empty'], $jsons));
    }

    //consider notnull
    //eg. nempty = [product_id, product_meta.ref1]
    if (isset($query['nempty'])) {
      $map = array_merge($map, $this->mapNempties($query['nempty'], $jsons));
    }

    return $map;
  }

  /**
   * Translates safe filter to serialized where
   *
   * @param array $filters
   * @param array $jsons
   *
   * @return array
   */
  public function mapFilters(array $filters, array $jsons): array
  {
    $map = [];

    foreach ($filters as $column => $value) {
      if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
        $map[] = [
          'where' => $column . ' = %s', 'binds' => [$value]
        ];
        continue;
      }

      //by chance is it a json filter?
      if (!preg_match('/^[a-zA-Z0-9-_]+\.[a-zA-Z0-9-_]+$/', $column)) {
        continue;
      }

      //get the name
      $name = substr($column, 0, strpos($column, '.'));
      //name should be a json column type
      if (!in_array($name, $jsons)) {
        continue;
      }

      //this is like product_attributes.HDD
      $path = substr($column, strpos($column, '.'));
      $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);
      $path = preg_replace('/([^\.]+\s[^\.]+)/', '""$1""', $path);
      $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);
      $map[] = [
        'where' => $column . ' = %s', 'binds' => [$value]
      ];
    }

    return $map;
  }

  /**
   * Translates safe likes to serialized where
   *
   * @param array $filters
   * @param array $jsons
   *
   * @return array
   */
  public function mapLikes(array $filters, array $jsons): array
  {
    $map = [];

    foreach ($filters as $column => $value) {
      if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
        $map[] = [
          'where' => $column . ' LIKE %s', 'binds' => [$value]
        ];
        continue;
      }

      //by chance is it a json filter?
      if (!preg_match('/^[a-zA-Z0-9-_]+\.[a-zA-Z0-9-_]+$/', $column)) {
        continue;
      }

      //get the name
      $name = substr($column, 0, strpos($column, '.'));
      //name should be a json column type
      if (!in_array($name, $jsons)) {
        continue;
      }

      //this is like product_attributes.HDD
      $path = substr($column, strpos($column, '.'));
      $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);
      $path = preg_replace('/([^\.]+\s[^\.]+)/', '""$1""', $path);
      $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);
      $map[] = [
        'where' => $column . ' LIKE %s', 'binds' => [$value]
      ];
    }

    return $map;
  }

  /**
   * Translates safe ins to serialized where
   *
   * @param array $filters
   * @param array $jsons
   *
   * @return array
   */
  public function mapIns(array $filters, array $jsons): array
  {
    $map = [];

    foreach ($filters as $column => $value) {
      //make sure value is an array
      if (!is_array($value)) {
        $value = [$value];
      }

      //this is like if an array has one of items in another array
      // eg. if product_tags has one of these values [foo, bar, etc.]
      if (in_array($column, $jsons)) {
        $or = [];
        $where = [];
        foreach ($value as $option) {
          $where[] = "JSON_SEARCH(LOWER($column), 'one', %s) IS NOT NULL";
          $or[] = '%' . strtolower($option) . '%';
        }

        $map[] = [
          'where' => '(' . implode(' OR ', $where) . ')',
          'binds' => $or
        ];
        continue;
      }

      //this is the normal
      if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
        $binds = implode(', ', array_fill(0, count($value), '%s'));
        $map[] = [
          'where' => sprintf('%s IN (%s)', $column, $binds),
          'binds' => $value
        ];
        continue;
      }

      //by chance is it a json filter?
      if (!preg_match('/^[a-zA-Z0-9-_]+\.[a-zA-Z0-9-_]+$/', $column)) {
        continue;
      }

      //get the name
      $name = substr($column, 0, strpos($column, '.'));
      //name should be a json column type
      if (!in_array($name, $jsons)) {
        continue;
      }

      //this is like product_attributes.HDD has
      //one of these values [foo, bar, etc.]
      $path = substr($column, strpos($column, '.'));
      $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);
      $path = preg_replace('/([^\.]+\s[^\.]+)/', '""$1""', $path);
      $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);
      $where = array_fill(0, count($value), $column . ' = %s');
      $map[] = [
        'where' => '(' . implode(' OR ', $where) . ')',
        'binds' => $value
      ];
    }

    return $map;
  }

  /**
   * Translates safe q to serialized where
   *
   * @param array $filters
   * @param array $jsons
   *
   * @return array
   */
  public function mapKeywords(array $filters, array $searchable): array
  {
    $map = [];

    foreach ($filters as $keyword) {
      $binds = $where = [];
      foreach ($searchable as $name => $field) {
        $where[] = sprintf('LOWER(%s) LIKE %%s', $name);
        $binds[] = sprintf('%%%s%%', strtolower($keyword));
      }

      $map[] = [
        'where' => sprintf('(%s)', implode(' OR ', $where)),
        'binds' => $binds
      ];
    }

    return $map;
  }

  /**
   * Translates safe spans to serialized where
   *
   * @param array $filters
   * @param array $jsons
   *
   * @return array
   */
  public function mapSpans(array $filters, array $jsons): array
  {
    $map = [];

    foreach ($filters as $column => $value) {
      if (!is_array($value) || empty($value)) {
        continue;
      }

      if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
        // minimum?
        if (isset($value[0]) && !empty($value[0])) {
          $map[] = [
            'where' => $column . ' >= %s', 'binds' => [$value[0]]
          ];
        }

        // maximum?
        if (isset($value[1]) && !empty($value[1])) {
          $map[] = [
            'where' => $column . ' <= %s', 'binds' => [$value[1]]
          ];
        }

        continue;
      }

      //by chance is it a json filter?
      if (strpos($column, '.') === false) {
        continue;
      }

      //get the name
      $name = substr($column, 0, strpos($column, '.'));
      //name should be a json column type
      if (!in_array($name, $jsons)) {
        continue;
      }

      //this is like product_attributes.HDD
      $path = substr($column, strpos($column, '.'));
      $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);
      $path = preg_replace('/([^\.]+\s[^\.]+)/', '""$1""', $path);
      $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);

      // minimum?
      if (isset($value[0]) && !empty($value[0])) {
        $map[] = [
          'where' => $column . ' >= %s', 'binds' => [$value[0]]
        ];
      }

      // maximum?
      if (isset($value[1]) && !empty($value[1])) {
        $map[] = [
          'where' => $column . ' <= %s', 'binds' => [$value[1]]
        ];
      }
    }

    return $map;
  }

  /**
   * Translates safe empties to serialized where
   *
   * @param array $filters
   * @param array $jsons
   *
   * @return array
   */
  public function mapEmpties(array $filters, array $jsons): array
  {
    $map = [];

    foreach ($filters as $column) {
      if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
        $map[] = [
          'where' => $column . ' IS NULL', 'binds' => []
        ];
        continue;
      }

      //by chance is it a json filter?
      if (!preg_match('/^[a-zA-Z0-9-_]+\.[a-zA-Z0-9-_]+$/', $column)) {
        continue;
      }

      //get the name
      $name = substr($column, 0, strpos($column, '.'));
      //name should be a json column type
      if (!in_array($name, $jsons)) {
        continue;
      }

      //this is like product_attributes.HDD
      $path = substr($column, strpos($column, '.'));
      $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);
      $path = preg_replace('/([^\.]+\s[^\.]+)/', '""$1""', $path);
      $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);
      $map[] = [
        'where' => $column . ' IS NULL', 'binds' => []
      ];
    }

    return $map;
  }

  /**
   * Translates safe nempties to serialized where
   *
   * @param array $filters
   * @param array $jsons
   *
   * @return array
   */
  public function mapNempties(array $filters, array $jsons): array
  {
    $map = [];

    foreach ($filters as $column) {
      if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
        $map[] = [
          'where' => $column . ' IS NOT NULL', 'binds' => []
        ];
        continue;
      }

      //by chance is it a json filter?
      if (!preg_match('/^[a-zA-Z0-9-_]+\.[a-zA-Z0-9-_]+$/', $column)) {
        continue;
      }

      //get the name
      $name = substr($column, 0, strpos($column, '.'));
      //name should be a json column type
      if (!in_array($name, $jsons)) {
        continue;
      }

      //this is like product_attributes.HDD
      $path = substr($column, strpos($column, '.'));
      $path = preg_replace('/\.*([0-9]+)/', '[$1]', $path);
      $path = preg_replace('/([^\.]+\s[^\.]+)/', '""$1""', $path);
      $column = sprintf('JSON_EXTRACT(%s, "$%s")', $name, $path);
      $map[] = [
        'where' => $column . ' IS NOT NULL', 'binds' => []
      ];
    }

    return $map;
  }

  /**
   * Groups results by their table prefix ie. [table]_column
   *
   * @param mixed $filter
   *
   * @return array
   */
  public function organizeRow(array $results): array
  {
    $organized = [];
    foreach ($results as $key => $value) {
      if (strpos($key, '_') === false) {
        $organized[$key] = $value;
        continue;
      }

      $group = substr($key, 0, strpos($key, '_'));
      $organized[$group][$key] = $value;
    }

    return $organized;
  }

  /**
   * Sets the fieldset folder
   *
   * @param *PackageHandler $handler
   */
  public function setFieldsetFolder(string $folder): SystemPackage
  {
    Fieldset::setFolder($folder);
    return $this;
  }

  /**
   * Sets the schema folder
   *
   * @param *PackageHandler $handler
   */
  public function setSchemaFolder(string $folder): SystemPackage
  {
    Schema::setFolder($folder);
    return $this;
  }
}
