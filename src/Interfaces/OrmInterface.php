<?php

namespace Jxckaroo\Simpl\Interfaces;


/**
 * Class Orm
 * @package Jxckaroo\Simpl
 */
interface OrmInterface
{
    public function update();

    public function delete();

    /**
     * Get current object to array or return specific field value.
     *
     * @param bool $field
     * @return array
     */
    public function get($field = false);

    /**
     * Executed before any new records are created
     * Placeholder for subclasses
     */
    public function preInsert();

    /**
     * Fetch column names of active table
     *
     * @access public
     * @return array
     */
    public function getColumnNames();

    public function useConnection($pdo);

    public function save();

    public function isNewLoad();

    public function initialise();
}