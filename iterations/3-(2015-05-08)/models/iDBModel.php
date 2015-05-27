<?php namespace iNeed;

use GuzzleHttp\Message\ResponseInterface;

interface iDBModel {
    /**
     * Commits this object to Team 10's database.
     *
     * Returns a GuzzleHttp ResponseInterface that has information about the POST request.
     * @return ResponseInterface object
     */
    public function commit();

    /**
     * Function to see if this instance of the model has been committed to the database.
     * @return Bool True if it has, false if it hasnt
     */
    public function hasCommitted();
}