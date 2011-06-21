<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * MemcacheMock - for server doesn't support memcache
 *
 * @author ryu
 */
class MemcacheMock {
    public function get() {}
    public function set() {}
    public function connect() {}
    public function flush() {}
    public function close() {}
}
?>
