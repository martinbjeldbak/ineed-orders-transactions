<?php namespace iNeed;

interface iDBModel {
    /**
     * Commits this object to Team 10's database.
     *
     * Returns a GuzzleHttp ResponseInterface that has information about the POST request.
     */
    public function commit();
}