<?php

namespace sokyrko\yii\salesforce\data;

/**
 * Class QueryBuilder
 *
 * @package sokyrko\yii\salesforce\data
 */
class QueryBuilder extends \Akeneo\SalesForce\Query\QueryBuilder
{
    /**
     * @param int $limit
     *
     * @return $this
     */
    public function limit($limit)
    {
        $this->query = sprintf('%s LIMIT %s', $this->query, $limit);

        return $this;
    }

    /**
     * @param int $offset
     *
     * @return $this
     */
    public function offset($offset)
    {
        $this->query = sprintf('%s OFFSET %s', $this->query, $offset);

        return $this;
    }
}
