<?php

    class Db {
        
        private $con;
        
        public function __construct(){
            $this->con = new mysqli("localhost", "root", "", "apiv1");
        }
        
        public function query($sql){
            return $this->con->query($sql);
        }
        
    }