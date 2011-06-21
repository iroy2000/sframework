<?php
/**
 * Concept - There will be a cache and cache buffer folder, the old cache will be served
 *           until the cache buffer finished generating and overwrite it.
 * @interface Cache
 * @author ryu
 *
 */
interface Cache {
    // validation
    public function isExpired();
    public function isExisted();

    // CRUD
    public function create();
    public function update();
    public function retrieve();
    public function delete();

}