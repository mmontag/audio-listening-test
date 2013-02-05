<?php
/**
 * Deterministic key-based shuffle for PHP
 * See http://stackoverflow.com/questions/3169805/how-can-i-randomize-an-array-in-php-by-providing-a-seed-and-get-the-same-order
 */

function shuf(&$items, $seed) {
  @mt_srand($seed);
  for ($i = count($items) - 1; $i > 0; $i--) {
    $j = @mt_rand(0, $i);
    $tmp = $items[$i];
    $items[$i] = $items[$j];
    $items[$j] = $tmp;
  }
}
?>