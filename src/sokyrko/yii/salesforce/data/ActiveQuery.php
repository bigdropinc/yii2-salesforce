<?php
/**
 * Created by PhpStorm.
 * User: bigdrop
 * Date: 28.07.17
 * Time: 16:21
 */

namespace sokyrko\yii\salesforce\data;

use Akeneo\SalesForce\Connector\SalesForceClient;
use Akeneo\SalesForce\Query\QueryBuilder;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveRecordInterface;
use yii\db\Connection;
use yii\helpers\ArrayHelper;

/**
 * Class ActiveQuery
 *
 * @package sokyrko\yii\salesforce\data
 */
class ActiveQuery implements ActiveQueryInterface
{
    /** @var ActiveRecord */
    protected $modelClass;

    /** @var QueryBuilder */
    protected $salesForceQuery;

    /** @var boolean */
    protected $enableEmulateExecution;

    protected $queryParts = [
        'select'  => [],
        'from'    => '',
        'where'   => [],
        'orderBy' => [],
        'limit'   => '',
    ];

    // TODO: refactor query builder to works if andWhere called without first where

    /**
     * Constructor.
     *
     * @param string $modelClass the model class associated with this query
     * @param array  $config configurations to be applied to the newly created query object
     */
    public function __construct($modelClass, $config = [])
    {
        $this->modelClass = $modelClass;
        $this->salesForceQuery = new QueryBuilder();

        $this->queryParts['from'] = $this->modelClass::tableName();
        $this->queryParts['select'] = array_keys(get_class_vars($modelClass));
    }

    /**
     * @param $what array|string
     * @return $this
     */
    public function select($what)
    {
        $this->queryParts['select'] = is_array($what) ? $what : [$what];

        return $this;
    }

    /**
     * @param $what string|array
     * @return string
     */
    protected function parseSelect($what)
    {
        if (is_array($what)) {
            return implode(', ', $what);
        }

        if (is_string($what)) {
            return $what;
        }

        throw new InvalidParamException();
    }


    /**
     * @inheritdoc
     */
    public function asArray($value = true)
    {
        throw new InvalidCallException('Not implemented yet.'); // TODO
    }

    /**
     * Executes query and returns a single row of result.
     *
     * @param Connection $db the DB connection used to create the DB command.
     * If `null`, the DB connection returned by [[ActiveQueryTrait::$modelClass|modelClass]] will be used.
     * @return ActiveRecordInterface|array|null a single row of query result. Depending on the setting of [[asArray]],
     * the query result may be either an array or an ActiveRecord object. `null` will be returned
     * if the query results in nothing.
     */
    public function one($db = null)
    {
        $all = $this->all();

        return reset($all); // todo: refactor
    }

    /**
     * Sets the [[indexBy]] property.
     *
     * @param string|callable $column the name of the column by which the query results should be indexed by.
     * This can also be a callable (e.g. anonymous function) that returns the index value based on the given
     * row or model data. The signature of the callable should be:
     *
     * ```php
     * // $model is an AR instance when `asArray` is false,
     * // or an array of column values when `asArray` is true.
     * function ($model)
     * {
     *     // return the index value corresponding to $model
     * }
     * ```
     *
     * @return $this the query object itself
     */
    public function indexBy($column)
    {
        throw new InvalidCallException('Not implemented yet.'); // TODO
    }

    /**
     * Specifies the relations with which this query should be performed.
     *
     * The parameters to this method can be either one or multiple strings, or a single array
     * of relation names and the optional callbacks to customize the relations.
     *
     * A relation name can refer to a relation defined in [[ActiveQueryTrait::modelClass|modelClass]]
     * or a sub-relation that stands for a relation of a related record.
     * For example, `orders.address` means the `address` relation defined
     * in the model class corresponding to the `orders` relation.
     *
     * The following are some usage examples:
     *
     * ```php
     * // find customers together with their orders and country
     * Customer::find()->with('orders', 'country')->all();
     * // find customers together with their orders and the orders' shipping address
     * Customer::find()->with('orders.address')->all();
     * // find customers together with their country and orders of status 1
     * Customer::find()->with([
     *     'orders' => function (\yii\db\ActiveQuery $query) {
     *         $query->andWhere('status = 1');
     *     },
     *     'country',
     * ])->all();
     * ```
     *
     * @return $this the query object itself
     */
    public function with()
    {
        throw new InvalidCallException('Not implemented yet.'); // TODO
    }

    /**
     * Specifies the relation associated with the junction table for use in relational query.
     *
     * @param string   $relationName the relation name. This refers to a relation declared in the [[ActiveRelationTrait::primaryModel|primaryModel]] of the relation.
     * @param callable $callable a PHP callback for customizing the relation associated with the junction table.
     * Its signature should be `function($query)`, where `$query` is the query to be customized.
     * @return $this the relation object itself.
     */
    public function via($relationName, callable $callable = null)
    {
        throw new InvalidCallException('Not implemented yet.'); // TODO
    }

    /**
     * Finds the related records for the specified primary record.
     * This method is invoked when a relation of an ActiveRecord is being accessed in a lazy fashion.
     *
     * @param string                $name the relation name
     * @param ActiveRecordInterface $model the primary model
     * @return mixed the related record(s)
     */
    public function findFor($name, $model)
    {
        throw new InvalidCallException('Not implemented yet.'); // TODO
    }

    /**
     * Executes the query and returns all results as an array.
     *
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `salesforce` application component will be used.
     * @return array the query results. If the query results in nothing, an empty array will be returned.
     */
    public function all($db = null)
    {
        /** @var SalesForceClient $client */
        $client = \Yii::$app->get('salesforce')->getClient();

        return array_map(function ($item) {
            return new $this->modelClass($item);
        }, $client->search($this->getRawQuery())); // TODO: create query and then search
    }

    /**
     * Returns raw SOQL query
     *
     * @return string
     */
    public function getRawQuery()
    {
        $q = $this->salesForceQuery
            ->select($this->parseSelect($this->queryParts['select']))
            ->from($this->queryParts['from']);

        if ($this->queryParts['where']) {
            $q->where($this->parseCondition(reset($this->queryParts['where'])));

            while ($condition = next($this->queryParts['where'])) {
                $q->andWhere($this->parseCondition($condition));
            }
        }

        if ($this->queryParts['orderBy']) {
            $q->orderBy($this->parseOrderBy($this->queryParts['orderBy']));
        }

        return $q->getQuery();
    }

    /**
     * @param $condition string|array
     * @return string
     */
    protected function parseOrderBy($condition)
    {
        if (is_string($condition)) {
            return $condition;
        }

        foreach ($condition as $column => $sort) {
            return sprintf('%s %s', $column, (is_integer($sort) ? $this->replaceSortConstWithString($sort) : $sort));
        }

        throw new InvalidParamException();
    }

    /**
     * @param $sortConst integer
     * @return string
     */
    protected function replaceSortConstWithString($sortConst)
    {
        return $sortConst == SORT_ASC ? 'ASC' : 'DESC';
    }

    /**
     * Returns the number of records.
     *
     * @param string     $q the COUNT expression. Defaults to '*'.
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `db` application component will be used.
     * @return int number of records.
     */
    public function count($q = '*', $db = null)
    {
        throw new InvalidCallException('Not implemented yet.'); // TODO
    }

    /**
     * Returns a value indicating whether the query result contains any row of data.
     *
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `db` application component will be used.
     * @return bool whether the query result contains any row of data.
     */
    public function exists($db = null)
    {
        throw new InvalidCallException('Not implemented yet.'); // TODO
    }

    /**
     * Sets the WHERE part of the query.
     *
     * The `$condition` specified as an array can be in one of the following two formats:
     *
     * - hash format: `['column1' => value1, 'column2' => value2, ...]`
     * - operator format: `[operator, operand1, operand2, ...]`
     *
     * A condition in hash format represents the following SQL expression in general:
     * `column1=value1 AND column2=value2 AND ...`. In case when a value is an array,
     * an `IN` expression will be generated. And if a value is `null`, `IS NULL` will be used
     * in the generated expression. Below are some examples:
     *
     * - `['type' => 1, 'status' => 2]` generates `(type = 1) AND (status = 2)`.
     * - `['id' => [1, 2, 3], 'status' => 2]` generates `(id IN (1, 2, 3)) AND (status = 2)`.
     * - `['status' => null]` generates `status IS NULL`.
     *
     * A condition in operator format generates the SQL expression according to the specified operator, which
     * can be one of the following:
     *
     * - **and**: the operands should be concatenated together using `AND`. For example,
     *   `['and', 'id=1', 'id=2']` will generate `id=1 AND id=2`. If an operand is an array,
     *   it will be converted into a string using the rules described here. For example,
     *   `['and', 'type=1', ['or', 'id=1', 'id=2']]` will generate `type=1 AND (id=1 OR id=2)`.
     *   The method will *not* do any quoting or escaping.
     *
     * - **or**: similar to the `and` operator except that the operands are concatenated using `OR`. For example,
     *   `['or', ['type' => [7, 8, 9]], ['id' => [1, 2, 3]]]` will generate `(type IN (7, 8, 9) OR (id IN (1, 2, 3)))`.
     *
     * - **not**: this will take only one operand and build the negation of it by prefixing the query string with `NOT`.
     *   For example `['not', ['attribute' => null]]` will result in the condition `NOT (attribute IS NULL)`.
     *
     * - **between**: operand 1 should be the column name, and operand 2 and 3 should be the
     *   starting and ending values of the range that the column is in.
     *   For example, `['between', 'id', 1, 10]` will generate `id BETWEEN 1 AND 10`.
     *
     * - **not between**: similar to `between` except the `BETWEEN` is replaced with `NOT BETWEEN`
     *   in the generated condition.
     *
     * - **in**: operand 1 should be a column or DB expression, and operand 2 be an array representing
     *   the range of the values that the column or DB expression should be in. For example,
     *   `['in', 'id', [1, 2, 3]]` will generate `id IN (1, 2, 3)`.
     *   The method will properly quote the column name and escape values in the range.
     *
     *   To create a composite `IN` condition you can use and array for the column name and value, where the values are indexed by the column name:
     *   `['in', ['id', 'name'], [['id' => 1, 'name' => 'foo'], ['id' => 2, 'name' => 'bar']] ]`.
     *
     *   You may also specify a sub-query that is used to get the values for the `IN`-condition:
     *   `['in', 'user_id', (new Query())->select('id')->from('users')->where(['active' => 1])]`
     *
     * - **not in**: similar to the `in` operator except that `IN` is replaced with `NOT IN` in the generated condition.
     *
     * - **like**: operand 1 should be a column or DB expression, and operand 2 be a string or an array representing
     *   the values that the column or DB expression should be like.
     *   For example, `['like', 'name', 'tester']` will generate `name LIKE '%tester%'`.
     *   When the value range is given as an array, multiple `LIKE` predicates will be generated and concatenated
     *   using `AND`. For example, `['like', 'name', ['test', 'sample']]` will generate
     *   `name LIKE '%test%' AND name LIKE '%sample%'`.
     *   The method will properly quote the column name and escape special characters in the values.
     *   Sometimes, you may want to add the percentage characters to the matching value by yourself, you may supply
     *   a third operand `false` to do so. For example, `['like', 'name', '%tester', false]` will generate `name LIKE '%tester'`.
     *
     * - **or like**: similar to the `like` operator except that `OR` is used to concatenate the `LIKE`
     *   predicates when operand 2 is an array.
     *
     * - **not like**: similar to the `like` operator except that `LIKE` is replaced with `NOT LIKE`
     *   in the generated condition.
     *
     * - **or not like**: similar to the `not like` operator except that `OR` is used to concatenate
     *   the `NOT LIKE` predicates.
     *
     * - **exists**: operand 1 is a query object that used to build an `EXISTS` condition. For example
     *   `['exists', (new Query())->select('id')->from('users')->where(['active' => 1])]` will result in the following SQL expression:
     *   `EXISTS (SELECT "id" FROM "users" WHERE "active"=1)`.
     *
     * - **not exists**: similar to the `exists` operator except that `EXISTS` is replaced with `NOT EXISTS` in the generated condition.
     *
     * - Additionally you can specify arbitrary operators as follows: A condition of `['>=', 'id', 10]` will result in the
     *   following SQL expression: `id >= 10`.
     *
     * @param string|array $condition the conditions that should be put in the WHERE part.
     * @return $this the query object itself
     * @see andWhere()
     * @see orWhere()
     */
    public function where($condition)
    {
        $this->queryParts['where'] = [$condition];

        return $this;
    }

    /**
     * Adds an additional WHERE condition to the existing one.
     * The new condition and the existing one will be joined using the 'AND' operator.
     *
     * @param string|array $condition the new WHERE condition. Please refer to [[where()]]
     * on how to specify this parameter.
     * @return $this the query object itself
     * @see where()
     * @see orWhere()
     */
    public function andWhere($condition)
    {
        $this->queryParts['where'][] = $condition;

        return $this;
    }

    /**
     * Adds an additional WHERE condition to the existing one.
     * The new condition and the existing one will be joined using the 'OR' operator.
     *
     * @param string|array $condition the new WHERE condition. Please refer to [[where()]]
     * on how to specify this parameter.
     * @return $this the query object itself
     * @see where()
     * @see andWhere()
     */
    public function orWhere($condition)
    {
        // TODO

        return $this;
    }

    /**
     * Sets the WHERE part of the query ignoring empty parameters.
     *
     * @param array $condition the conditions that should be put in the WHERE part. Please refer to [[where()]]
     * on how to specify this parameter.
     * @return $this the query object itself
     * @see andFilterWhere()
     * @see orFilterWhere()
     */
    public function filterWhere(array $condition)
    {
        throw new InvalidCallException('Not implemented yet.'); // TODO
    }

    /**
     * Adds an additional WHERE condition to the existing one ignoring empty parameters.
     * The new condition and the existing one will be joined using the 'AND' operator.
     *
     * @param array $condition the new WHERE condition. Please refer to [[where()]]
     * on how to specify this parameter.
     * @return $this the query object itself
     * @see filterWhere()
     * @see orFilterWhere()
     */
    public function andFilterWhere(array $condition)
    {
        throw new InvalidCallException('Not implemented yet.'); // TODO
    }

    /**
     * Adds an additional WHERE condition to the existing one ignoring empty parameters.
     * The new condition and the existing one will be joined using the 'OR' operator.
     *
     * @param array $condition the new WHERE condition. Please refer to [[where()]]
     * on how to specify this parameter.
     * @return $this the query object itself
     * @see filterWhere()
     * @see andFilterWhere()
     */
    public function orFilterWhere(array $condition)
    {
        throw new InvalidCallException('Not implemented yet.'); // TODO
    }

    /**
     * Sets the ORDER BY part of the query.
     *
     * @param string|array $columns the columns (and the directions) to be ordered by.
     * Columns can be specified in either a string (e.g. "id ASC, name DESC") or an array
     * (e.g. `['id' => SORT_ASC, 'name' => SORT_DESC]`).
     * The method will automatically quote the column names unless a column contains some parenthesis
     * (which means the column contains a DB expression).
     * @return $this the query object itself
     * @see addOrderBy()
     */
    public function orderBy($columns)
    {
        $this->queryParts['orderBy'] = $columns;

        return $this;
    }

    /**
     * Adds additional ORDER BY columns to the query.
     *
     * @param string|array $columns the columns (and the directions) to be ordered by.
     * Columns can be specified in either a string (e.g. "id ASC, name DESC") or an array
     * (e.g. `['id' => SORT_ASC, 'name' => SORT_DESC]`).
     * The method will automatically quote the column names unless a column contains some parenthesis
     * (which means the column contains a DB expression).
     * @return $this the query object itself
     * @see orderBy()
     */
    public function addOrderBy($columns)
    {
        throw new InvalidCallException('Not implemented yet.'); // TODO
    }

    /**
     * Sets the LIMIT part of the query.
     *
     * @param int|null $limit the limit. Use null or negative value to disable limit.
     * @return $this the query object itself
     */
    public function limit($limit)
    {
        $this->queryParts['limit'][] = $limit;

        return $this;
    }

    /**
     * Sets the OFFSET part of the query.
     *
     * @param int|null $offset the offset. Use null or negative value to disable offset.
     * @return $this the query object itself
     */
    public function offset($offset)
    {
        throw new InvalidCallException('Not implemented yet.');
    }

    /**
     * Sets whether to emulate query execution, preventing any interaction with data storage.
     * After this mode is enabled, methods, returning query results like [[one()]], [[all()]], [[exists()]]
     * and so on, will return empty or false values.
     * You should use this method in case your program logic indicates query should not return any results, like
     * in case you set false where condition like `0=1`.
     *
     * @param bool $value whether to prevent query execution.
     * @return $this the query object itself.
     * @since 2.0.11
     */
    public function emulateExecution($value = true)
    {
        $this->enableEmulateExecution = $value;

        return $this;
    }

    /**
     * @param string|array $condition
     * @return mixed
     */
    protected function parseCondition($condition)
    {
        if (is_string($condition)) { // TODO: improve as in yii
            return $condition;
        }

        if (is_array($condition)) {
            if (!ArrayHelper::isAssociative($condition)) {
                $sign = reset($condition);
                $column = next($condition);
                $value = next($condition);

                switch (strtolower($sign)) {
                    case '!=':
                        return $this->salesForceQuery->getNotEqualCondition($column, $value);
                    case '=':
                    case '==':
                        return $this->salesForceQuery->getEqualCondition($column, $value);
                    case 'like':
                        return $this->salesForceQuery->getLikeCondition($column, $value);
                    default:
                        throw new InvalidParamException('Only !=, =, like allowed.');
                }
            }

            foreach ($condition as $key => $value) {
                if (is_integer($key)) {
                    return $this->parseCondition($value);
                } else {
                    return $this->salesForceQuery->getEqualCondition($key, $value);
                }
            }
        }

        throw new \InvalidArgumentException();
    }
}
