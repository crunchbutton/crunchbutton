<?php

$r = new Grubhub_Restaurant();

foreach ($r->dishes() as $dish) {
	echo '<b>'.$dish->name.'</b>&nbsp;&nbsp;&nbsp;&nbsp;('.$dish->price.')<br>';
}