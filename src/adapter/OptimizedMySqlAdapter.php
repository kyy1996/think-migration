<?php
/**
 * Created by PhpStorm.
 * User: alen
 * Date: 12/02/2018
 * Time: 10:09 PM
 */

namespace think\migration\adapter;


use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Db\Table;
use Phinx\Db\Table\Column;

class OptimizedMySqlAdapter extends MysqlAdapter
{
    /**
     * {@inheritdoc}
     */
    public function createTable(Table $table)
    {
        // This method is based on the MySQL docs here: http://dev.mysql.com/doc/refman/5.1/en/create-index.html
        $defaultOptions = [
            'engine'    => 'InnoDB',
            'collation' => 'utf8_general_ci'
        ];

        $options = array_merge(
            $defaultOptions,
            array_intersect_key($this->getOptions(), $defaultOptions),
            $table->getOptions()
        );

        // Add the default primary key
        $columns = $table->getPendingColumns();
        if (!isset($options['id']) || (isset($options['id']) && $options['id'] === true)) {
            $options['id'] = 'id';
        }
        if (isset($options['id']) && is_string($options['id'])) {
            // Handle id => "field_name" to support AUTO_INCREMENT
            $column = new Column();
            $column->setName($options['id'])
                   ->setType('integer')
                   ->setSigned(isset($options['signed']) ? $options['signed'] : true)
                   ->setIdentity(true);

            array_unshift($columns, $column);
            $options['primary_key'] = $options['id'];
        }


        // process table engine (default to InnoDB)
        $optionsStr = 'ENGINE = InnoDB';
        if (isset($options['engine'])) {
            $optionsStr = sprintf('ENGINE = %s', $options['engine']);
        }

        // process table collation
        if (isset($options['collation'])) {
            $charset    = explode('_', $options['collation']);
            $optionsStr .= sprintf(' CHARACTER SET %s', $charset[0]);
            $optionsStr .= sprintf(' COLLATE %s', $options['collation']);
        }

        // set the table comment
        if (isset($options['comment'])) {
            $optionsStr .= sprintf(" COMMENT=%s ", $this->getConnection()->quote($options['comment']));
        }

        $sql = 'CREATE TABLE ';
        $sql .= $this->quoteTableName($table->getName()) . ' (';
        foreach ($columns as $column) {
            $sql .= $this->quoteColumnName($column->getName()) . ' ' . $this->getColumnSqlDefinition($column) . ', ';
        }

        // set the primary key(s)
        if (isset($options['primary_key'])) {
            $sql = rtrim($sql);
            $sql .= ' PRIMARY KEY (';
            if (is_string($options['primary_key'])) { // handle primary_key => 'id'
                $sql .= $this->quoteColumnName($options['primary_key']);
            } else if (is_array($options['primary_key'])) { // handle primary_key => array('tag_id', 'resource_id')
                $sql .= implode(',', array_map([$this, 'quoteColumnName'], $options['primary_key']));
            }
            $sql .= ')';
        } else {
            $sql = substr(rtrim($sql), 0, -1); // no primary keys
        }

        // set the indexes
        $indexes = $table->getIndexes();
        foreach ($indexes as $index) {
            $sql .= ', ' . $this->getIndexSqlDefinition($index);
        }

        // set the foreign keys
        $foreignKeys = $table->getForeignKeys();
        foreach ($foreignKeys as $foreignKey) {
            $sql .= ', ' . $this->getForeignKeySqlDefinition($foreignKey);
        }

        $sql .= ') ' . $optionsStr;
        $sql = rtrim($sql) . ';';

        // execute the sql
        $this->execute($sql);
    }
}