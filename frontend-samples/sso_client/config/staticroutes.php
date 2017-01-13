<?php 

return [
    1 => [
        "id" => 1,
        "name" => "auth",
        "pattern" => "#^/auth/([\\w-]+)/?#",
        "reverse" => "/auth/%action",
        "module" => NULL,
        "controller" => "auth",
        "action" => "%action",
        "variables" => "action",
        "defaults" => NULL,
        "siteId" => NULL,
        "priority" => 0
    ]
];
