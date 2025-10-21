<?php


$cpuinfo = @file_get_contents("/proc/cpuinfo");
if ($cpuinfo) {
    $cores = substr_count($cpuinfo, "processor\t:");
    echo "CPU Cores: " . $cores;
} else {
    echo "Cannot read /proc/cpuinfo (restricted hosting).";
}
echo "<br>";
echo "PHP Memory Limit: " . ini_get("memory_limit");
//phpinfo();
