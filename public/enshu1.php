<?php
$r = new Redis();
$r->connect('redis', 6379, 1.5);
$cnt = $r->incr('access_count');
echo "access_count = " . htmlspecialchars((string)$cnt, ENT_QUOTES);

