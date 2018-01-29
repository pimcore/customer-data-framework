<?php

namespace CustomerManagementFrameworkBundle\Model\ActivityList;

class MySqlActivityList extends DefaultMariaDbActivityList
{
    public function __construct()
    {
        parent::__construct();
        $this->dao = new MySqlActivityList($this);
    }
}