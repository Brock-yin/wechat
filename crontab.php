<?php
$time = time();
file_put_contents('./crontab.txt', $time);